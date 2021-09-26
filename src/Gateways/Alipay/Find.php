<?php
/**
 * 单笔查询接口
 * document link: https://global.alipay.com/docs/ac/global/single_trade_query_cn
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-24 12:31
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

use Pudongping\GlobalPay\Supports\Config;
use Pudongping\GlobalPay\Supports\Encipher;
use Psr\Http\Message\ResponseInterface;
use Pudongping\GlobalPay\Events;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Pudongping\GlobalPay\Exceptions\InvalidArgumentException;

class Find
{

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function buildData(array $payload)
    {
        $isValidate = false;
        $data = [
            'service' => $this->getService(),  // 必填：接口名称，固定值
            'partner' => trim($this->config->get('partner')),  // 必填：签约的支付宝账号对应的支付宝唯一用户号。以 2088 开头的 16 位纯数字组成。
            '_input_charset' => $this->config->get('input_charset', 'utf-8'),  // 必填：商户网站使用的编码格式，如utf-8、gbk、gb2312等。
            'sign_type' => $this->config->get('sign_type', 'RSA'),  // 必填：签名方式只支持DSA、RSA、MD5。
        ];

        // 可选：支付宝合作商户网站唯一订单号（确保在商户系统中唯一）。
        if ($outTradeNo = Arr::get($payload, 'out_trade_no')) {
            $data['out_trade_no'] = $outTradeNo;
            $isValidate = true;
        }

        // 支付宝根据商户请求，创建订单生成的支付宝交易号。
        // 最短16位，最长64位。
        // 建议使用支付宝交易号进行查询，用商户网站唯一订单号查询的效率比较低。
        if ($tradeNo = Arr::get($payload, 'trade_no')) {
            $data['trade_no'] = $tradeNo;
            $isValidate = true;
        }

        if (! $isValidate) {
            throw new InvalidArgumentException('out_trade_no or trade_no field must exists');
        }

        // 如果为花呗分期支付的订单时
        if (Arr::get($payload, 'is_hbfq')) {
            $data['query_options'] = 'hbfq';
        }

        $data['sign'] = Encipher::sign($data, $data['sign_type'], $this->config->get('private_key'));

        return $data;
    }

    private function getService()
    {
        return 'single_trade_query';
    }

    public function makeResponse($result): Collection
    {
        $xml = $result;
        if ($result instanceof ResponseInterface) {
            $xml = simplexml_load_string($result->getBody()->getContents());
        }

        $data = json_decode(json_encode($xml), true);

        Events::dispatch(new Events\ApiRequestedEvent('Alipay', 'Find', Support::getInstance()->getBaseUri(), $data));

        $ret =  [
            'is_success' => Arr::get($data, 'is_success'),
            'sign' => Arr::get($data, 'sign'),
            'sign_type' => Arr::get($data, 'sign_type'),
            'params' => Arr::get($data, 'response.trade'),
        ];

        return new Collection($ret);
    }

}