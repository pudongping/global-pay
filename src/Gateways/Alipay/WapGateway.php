<?php
/**
 * 手机网站支付接口
 * document link: https://global.alipay.com/docs/ac/global/create_forex_trade_wap_cn
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-24 12:00
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

class WapGateway extends WebGateway
{

    protected function getService()
    {
        return 'create_forex_trade_wap';
    }

    protected function getProductCode()
    {
        return 'NEW_WAP_OVERSEAS_SELLER';
    }

}