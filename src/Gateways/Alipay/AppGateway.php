<?php
/**
 * document link: https://global.alipay.com/docs/ac/global/mobile_securitypay_pay_cn
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-23 15:03
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

use Illuminate\Support\Arr;
use Pudongping\GlobalPay\Events;
use Symfony\Component\HttpFoundation\Response;

class AppGateway extends GatewayAbstract
{

    public function pay($endpoint, array $payload): Response
    {
        $params = $this->buildData($payload);

        Events::dispatch(new Events\PayStartedEvent('Alipay', 'App', $endpoint, $params));

        return new Response(http_build_query($params));
    }

    protected function buildData(array $payload)
    {
        $data = [
            'service' => $this->getService(),  // 必填：接口名称，固定值
            'partner' => trim($this->config->get('partner')),  // 必填：签约的支付宝账号对应的支付宝唯一用户号。以 2088 开头的 16 位纯数字组成。
            '_input_charset' => 'utf-8',  // 必填：商户网站使用的编码格式，固定为utf-8。
            'sign_type' => $this->config->get('sign_type', 'RSA'),  // 必填：签名类型，目前支持RSA，RSA2。备注：RSA2不可用于同步返回验签。
            // 'sign' => '',  // 必填：签名值
            'notify_url' => $this->config->get('notify_url', ''),  // 可选：支付宝服务器主动通知商户网站里指定的页面http路径。
            'refer_url' => $this->config->get('refer_url', ''),  // 必填：二级商户网站地址
            'out_trade_no' => $payload['out_trade_no'],  // 必填：支付宝合作商户网站唯一订单号。
            'subject' => $payload['subject'],  // 必填：商品的简要介绍。特殊字符不支持。备注：本字段的值会在客户支付时被展示给客户。
            'payment_type' => '1',  // 必填：支付类型。默认值为：1（商品购买）。
            'seller_id' => trim($this->config->get('seller_email')),  // 必填：卖家支付宝账号（邮箱或手机号码格式）或其对应的支付宝唯一用户号（以 2088 开头的纯 16 位数字）
            // 'total_fee' => $payload['total_fee'],  // 当 rmb_fee 为空时此参数不可空，商品的外币金额，范围：0.01～1000000.00
            'currency' => $payload['currency'],  // 必填：币种，见7.1支持的币种列表
            'rmb_fee' => $payload['rmb_fee'],  // 当 total_fee 为空时，此参数不可空，该笔订单的资金总额，单位为 RMB-Yuan。取值范围为[0.01，100000000.00]，精确到小数点后两位。
            'forex_biz' => 'FP',  // 必填：只填 FP
            'product_code' => $this->getProductCode(),  // 必填
            'trade_information' => $payload['trade_information'],  // 必填：交易信息 https://global.alipay.com/doc/global/mobile_securitypay_pay_cn#diRMf
        ];

        $splitFundInfo = Arr::get($payload, 'split_fund_info');
        if ($splitFundInfo) {
            $data['split_fund_info'] = $splitFundInfo;
        }

        // 花呗分期相关参数
        if ($hbFqParam = $this->getHbFqParam($payload)) {
            $data['hb_fq_param'] = $hbFqParam;
        }
        // 花呗分期开启订单传参贴息活动（不支持 PC 支付，无论是国际还是国内的交易都不支持）
        if ($orderSubsidy = $this->getHbFqOrderSubsidy($payload)) {
            $data['business_params'] = $orderSubsidy;
        }

        $data['sign'] = $this->sign($data, $data['sign_type']);

        return $data;
    }

    private function getService()
    {
        return 'mobile.securitypay.pay';
    }

    private function getProductCode()
    {
        return 'NEW_WAP_OVERSEAS_SELLER';
    }

}