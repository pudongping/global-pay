<?php
/**
 * 无效网关错误
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 22:36
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Exceptions;

class InvalidGatewayException extends Exception
{

    public function __construct(string $message = '', $extra = null)
    {
        parent::__construct('INVALID_GATEWAY ' . $message, self::INVALID_GATEWAY, $extra);
    }

}