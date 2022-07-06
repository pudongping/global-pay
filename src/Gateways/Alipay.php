<?php
/**
 * 境外支付宝支付
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 22:47
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways;

use Pudongping\GlobalPay\Supports\Encipher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pudongping\GlobalPay\Contracts\GatewayApplicationInterface;
use Pudongping\GlobalPay\Contracts\GatewayInterface;
use Pudongping\GlobalPay\Events;
use Pudongping\GlobalPay\Exceptions\InvalidArgumentException;
use Pudongping\GlobalPay\Exceptions\InvalidConfigException;
use Pudongping\GlobalPay\Exceptions\InvalidGatewayException;
use Pudongping\GlobalPay\Exceptions\InvalidSignException;
use Pudongping\GlobalPay\Exceptions\HttpException;
use Pudongping\GlobalPay\Supports\Config;
use Pudongping\GlobalPay\Gateways\Alipay\Support;
use Pudongping\GlobalPay\Gateways\Alipay\Find;
use Pudongping\GlobalPay\Gateways\Alipay\Refund;
use Pudongping\GlobalPay\Gateways\Alipay\ExchangeRate;
use Pudongping\GlobalPay\Gateways\Alipay\HbFqCost;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

/**
 * @method Response   app(array $payload)      APP 支付
 * @method Response   wap(array $payload)      手机网站支付
 * @method Response   web(array $payload)      电脑支付
 */
class Alipay implements GatewayApplicationInterface
{

    /**
     * 普通模式
     */
    const MODE_NORMAL = 'normal';

    /**
     * 沙箱模式
     */
    const MODE_DEV = 'dev';

    public static $URL = [
        self::MODE_NORMAL => 'https://intlmapi.alipay.com/gateway.do',
        self::MODE_DEV => 'https://mapi.alipaydev.com/gateway.do',
    ];

    protected $config;

    /**
     * Alipay gateway
     *
     * @var string
     */
    protected $gateway;

    public function __construct(Config $config)
    {
        $this->gateway = Support::create($config)->getBaseUri();
        $this->config = $config;
    }

    public function __call($method, $params)
    {
        return $this->pay($method, ...$params);
    }

    public function pay($gateway, $params = [])
    {
        Events::dispatch(new Events\PayStartingEvent('Alipay', $gateway, $params));

        $gateway = get_class($this) . '\\' . Str::studly($gateway) . 'Gateway';

        if (class_exists($gateway)) {
            return $this->makePay($gateway, $params);
        }

        throw new InvalidGatewayException("Pay Gateway [{$gateway}] not exists");

    }

    protected function makePay(string $gateway, array $params)
    {
        $payload = array_filter($params, function ($value) {
            return '' !== $value && ! is_null($value);
        });

        $app = new $gateway($this->config);

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->gateway, $payload);
        }

        throw new InvalidArgumentException("Pay gateway [{$gateway}] must be an instance of GatewayInterface");
    }

    /**
     * 订单查询
     *
     * @param array|string $order
     * @return Collection
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function find($order): Collection
    {
        $find = new Find($this->config);
        if (is_string($order)) {
            $params['out_trade_no'] = $order;
        } else {
            $params = $order;
        }
        $payload = $find->buildData($params);

        Events::dispatch(new Events\MethodCalledEvent('Alipay', 'Find', $this->gateway, $payload));

        try {

            $res = Support::getInstance()->httpClient()->post($this->gateway, [
                'query' => $payload
            ]);

        } catch (\Exception $exception) {
            throw new HttpException('订单查询网络请求异常', $exception->getTrace());
        }

        return $find->makeResponse($res);
    }

    /**
     * 退款
     *
     * @param array $order
     * @return Collection
     * @throws HttpException
     */
    public function refund(array $order): Collection
    {
        $refund = new Refund($this->config);

        $payload = $refund->buildData($order);

        Events::dispatch(new Events\MethodCalledEvent('Alipay', 'Refund', $this->gateway, $payload));

        try {
            $res = Support::getInstance()->httpClient()->post($this->gateway, [
                'query' => $payload
            ]);
        } catch (\Exception $exception) {
            throw new HttpException('退款操作网络请求异常', $exception->getTrace());
        }

        return $refund->makeResponse($res);
    }

    /**
     * 参数验签
     *
     * @param null $data
     * @return Collection
     * @throws InvalidSignException
     */
    public function verify($data = null): Collection
    {
        if (is_null($data)) {
            $request = Request::createFromGlobals();

            $data = $request->request->count() > 0 ? $request->request->all() : $request->query->all();
        }

        if (isset($data['fund_bill_list'])) {
            $data['fund_bill_list'] = htmlspecialchars_decode($data['fund_bill_list']);
        }

        Events::dispatch(new Events\RequestReceivedEvent('Alipay', 'verify', $data));

        if ('MD5' === $data['sign_type']) {
            $publicKey = $this->config->get('key');
        } else {
            $publicKey = $this->config->get('public_key');
        }

        if (! $publicKey) {
            throw new InvalidConfigException('Missing Alipay Config -- [ali_public_key]');
        }

        if (Encipher::verify($data, $data['sign_type'], $publicKey)) {
            return new Collection($data);
        }

        Events::dispatch(new Events\SignFailedEvent('Alipay', 'vefiry', $data));

        throw new InvalidSignException('Alipay sign verify failed', $data);
    }

    /**
     * 成功时返回
     *
     * @return Response
     */
    public function success()
    {
        Events::dispatch(new Events\MethodCalledEvent('Alipay', 'Success', $this->gateway));

        return new Response('success');
    }

    /**
     * 失败时返回
     *
     * @return Response
     */
    public function fail()
    {
        Events::dispatch(new Events\MethodCalledEvent('Alipay', 'Fail', $this->gateway));

        return new Response('fail');
    }

    /**
     * 获取汇率
     *
     * @return Collection  example ==> {"USD":"6.706400","THB":"0.190629","SGD":"4.985800","SEK":"0.782500","NOK":"0.792300","KRW":"0.006065","HKD":"0.864700","GBP":"8.865400","EUR":"7.412300","DKK":"0.997400","CHF":"6.836600","CAD":"5.178200","AUD":"5.092300","JPY":"0.059183"}
     * @throws HttpException
     */
    public function getExchangeRate(): Collection
    {
        $exchangeRate = new ExchangeRate($this->config);

        $payload = $exchangeRate->buildData();

        Events::dispatch(new Events\MethodCalledEvent('Alipay', 'GetExchangeRate', $this->gateway));

        try {

            $result = Support::getInstance()->httpClient()->post($this->gateway, [
                'query' => $payload
            ]);
            $content = $result->getBody()->getContents();
            $content = explode("\r\n", $content);

            $rates = [];
            foreach ($content as $value) {
                $itemArr = explode('|', trim($value));
                $rates[Arr::get($itemArr, 2, 'no_key')] = Arr::get($itemArr, 3, 'no_value');
            }

        } catch (\Exception $exception) {
            throw new HttpException('获取汇率网络请求异常', $exception->getTrace());
        }

        return new Collection($rates);
    }

    /**
     * 获取花呗分期计费情况
     *
     * @param float $totalAmount  本金
     * @param bool $isShowAll  是否显示每一期的还款数
     * @param bool $isSellerPercent  是否商家承担所有的手续费
     * @param array $customerRates  用户自定义分期费率
     * @return Collection
     */
    public function getHbFqCost(float $totalAmount, bool $isShowAll = false, bool $isSellerPercent = false, array $customerRates = []): Collection
    {
        $data = (new HbFqCost)->fetchHbFqCost($totalAmount, $isShowAll, $isSellerPercent, $customerRates);
        return new Collection($data);
    }

}