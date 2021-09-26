<?php
/**
 * 网站支付接口
 * document link: https://global.alipay.com/docs/ac/global/create_forex_trade_cn
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-24 09:54
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

use Illuminate\Support\Arr;
use Pudongping\GlobalPay\Events;
use Symfony\Component\HttpFoundation\Response;

class WebGateway extends GatewayAbstract
{

    public function pay($endpoint, array $payload): Response
    {
        $onlyArgs = false;
        if (isset($payload['_only_args'])) {
            $onlyArgs = boolval($payload['_only_args']);
            unset($payload['_only_args']);
        }

        $params = $this->buildData($payload);

        Events::dispatch(new Events\PayStartedEvent('Alipay', 'Web/Wap', $endpoint, $params));

        $data = $onlyArgs ? $this->buildPayOnlyArgs($endpoint, $params) : $this->buildPayHtml($endpoint, $params);

        return new Response($data);
    }

    protected function buildData(array $payload)
    {
        $data = [
            'service' => $this->getService(),  // 必填：接口名称，固定值
            'partner' => trim($this->config->get('partner')),  // 必填：境外商户在支付宝的用户ID. 2088开头的16位数字
            '_input_charset' => $this->config->get('input_charset', 'utf-8'),  // 必填：请求数据的编码集，支持UTF-8。
            'sign_type' => $this->config->get('sign_type', 'RSA'),  // 必填：签名算法 DSA, RSA, and MD5
            'notify_url' => $this->config->get('notify_url', ''),  // 可选：针对该交易支付成功之后的通知接收URL。
            'return_url' => $this->config->get('return_url', ''),  // 可选：交易付款成功之后，返回到商家网站的URL。
            'subject' => $payload['subject'],  // 必填：商品的简要介绍，特殊字符不支持。备注：本字段的值会在客户支付时被展示给客户。
            'out_trade_no' => $payload['out_trade_no'],  // 必填：境外商户交易号（确保在境外商户系统中唯一）
            'currency' => $payload['currency'],  // 必填：结算币种
            'rmb_fee' => $payload['rmb_fee'],  // 范围为0.01～1000000.00 如果商户网站使用人民币进行标价就是用这个参数来替换total_fee参数，rmb_fee和total_fee不能同时使用
            'refer_url' => $this->config->get('refer_url', ''),  // 必填：二级商户的网址。注：机构必填。
            'product_code' => $this->getProductCode(),  // 必填
            'trade_information' => $payload['trade_information'],  // 必填：交易信息 https://global.alipay.com/doc/global/create_forex_trade_cn#EOSI6
        ];

        // 分账信息，json 格式，具体请参见 [“分账明细说明”](https://global.alipay.com/docs/ac/web_cn/split)
        $splitFundInfo = Arr::get($payload, 'split_fund_info');
        if ($splitFundInfo) {
            $data['split_fund_info'] = $splitFundInfo;
        }

        // 花呗分期相关参数
        if ($hbFqParam = $this->getHbFqParam($payload)) {
            $data['hb_fq_param'] = $hbFqParam;
        }

        $data['sign'] = $this->sign($data, $data['sign_type']);

        return $data;
    }

    protected function getService()
    {
        return 'create_forex_trade';
    }

    protected function getProductCode()
    {
        return 'NEW_OVERSEAS_SELLER';
    }

    protected function buildPayHtml($endpoint, $payload)
    {
        // 必须给构造表单带上 utf-8 ，给网关带上 _input_charset 参数才行，不然任何中文参数都会爆炸
        $sHtml = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $sHtml .= "<form id='alipay_submit' name='alipay_submit' action='" . $endpoint . '?_input_charset=UTF-8' . "' method='POST'>";
        foreach ($payload as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['alipay_submit'].submit();</script>";

        return $sHtml;
    }

    protected function buildPayOnlyArgs($endpoint, $payload)
    {
        $action = $endpoint . '?_input_charset=UTF-8';
        $args = array_merge($payload, compact('action'));
        return json_encode($args, 256);
    }

}