<?php
/**
 * 退款接口
 * document link: https://global.alipay.com/docs/ac/global/forex_refund_cn
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-24 15:24
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

use Pudongping\GlobalPay\Supports\Config;
use Pudongping\GlobalPay\Supports\Encipher;
use Psr\Http\Message\ResponseInterface;
use Pudongping\GlobalPay\Events;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Refund
{

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function buildData(array $payload)
    {
        $type = 'app';
        if (isset($payload['type'])) {
            $type = $payload['type'];
            unset($payload['type']);
        }

        $data = [
            'service' => $this->getService(),  // 必填：接口名称，固定值
            'partner' => trim($this->config->get('partner')),  // 必填：境外商户在支付宝的用户ID. 2088开头的16位数字
            '_input_charset' => $this->config->get('input_charset', 'utf-8'),  // 必填：请求数据的编码集，支持UTF-8。
            'sign_type' => $this->config->get('sign_type', 'RSA'),  // 必填：签名算法 DSA, RSA, and MD5.
            'out_return_no' => Arr::get($payload, 'out_return_no'),  // 必填：外部退款请求的ID
            'out_trade_no' => Arr::get($payload, 'out_trade_no'),  // 必填：境外商户交易号（确保在境外商户系统中唯一）
            'return_rmb_amount' => $payload['return_rmb_amount'],  // 可选：人民币退款金额
            'currency' => $payload['currency'],  // 必填：外币币种
            'gmt_return' => date('Y-m-d H:i:s'),  // yyyyMMddHHmmss北京时间(+8)
            'product_code' => $this->getProductCode($type),
            'reason' => Arr::get($payload, 'reason'),  // 可选：退款原因
            'is_sync' => Arr::get($payload, 'is_sync', 'Y'),  // 退款请求同步或异步处理。取值：Y或N。默认值：N，异步处理。如果该值为Y，notify_url将无意义
        ];

        // 分账信息，json 格式，具体请参见 [“分账明细说明”](https://global.alipay.com/doc/web_cn/split)
        if ($splitFundInfo = Arr::get($payload, 'split_fund_info')) {
            $data['split_fund_info'] = $splitFundInfo;
        }

        if ('N' == $data['is_sync']) {
            $notifyUrl = Arr::get($payload, 'notify_url') ?: $this->config->get('notify_url');
            if ($notifyUrl) {
                $data['notify_url'] = $notifyUrl;  // 可选：针对该交易支付成功之后的通知接收URL。
            }
        }

        $data['sign'] = Encipher::sign($data, $data['sign_type'], $this->config->get('private_key'));

        return $data;
    }


    private function getService()
    {
        return 'forex_refund';
    }

    private function getProductCode(string $type)
    {
        // 网站支付: NEW_OVERSEAS_SELLER 手机浏览器或支付宝钱包支付: NEW_WAP_OVERSEAS_SELLER
        return 'pc' === $type ? 'NEW_OVERSEAS_SELLER' : 'NEW_WAP_OVERSEAS_SELLER';
    }

    public function makeResponse($result): Collection
    {
        $xml = $result;
        if ($result instanceof ResponseInterface) {
            $xml = simplexml_load_string($result->getBody()->getContents());
        }

        $data = json_decode(json_encode($xml), true);

        Events::dispatch(new Events\ApiRequestedEvent('Alipay', 'Refund', Support::getInstance()->getBaseUri(), $data));

        $ret =  [
            'is_success' => Arr::get($data, 'is_success'),
        ];

        return new Collection($ret);
    }

}