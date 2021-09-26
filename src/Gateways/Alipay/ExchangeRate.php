<?php
/**
 * 汇率查询接口
 * document link: https://global.alipay.com/docs/ac/global/forex_rate_file_cn
 *
 * 1、货币间的汇率会在北京时间每日 9：00 到 11:00 间变动一次；
 * 2、汇率每日获取上限为 100 次。
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-24 16:51
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

use Pudongping\GlobalPay\Supports\Config;
use Pudongping\GlobalPay\Supports\Encipher;

class ExchangeRate
{

    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function buildData()
    {
        $data = [
            'service' => $this->getService(),  // 必填：接口名称，固定值
            'partner' => trim($this->config->get('partner')),  // 必填：签约的支付宝账号对应的支付宝唯一用户号。以 2088 开头的 16 位纯数字组成。
            '_input_charset' => $this->config->get('input_charset', 'utf-8'),  // 必填：商户网站使用的编码格式，如utf-8、gbk、gb2312等。
            'sign_type' => $this->config->get('sign_type', 'RSA'),  // 必填：签名方式，MD5, RSA, DSA
        ];

        $data['sign'] = Encipher::sign($data, $data['sign_type'], $this->config->get('private_key'));

        return $data;
    }

    private function getService()
    {
        return 'forex_rate_file';
    }

}