<?php
/**
 * 签名无效错误
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 22:37
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Exceptions;

class InvalidSignException extends Exception
{

    public function __construct(string $message = '', $extra = null)
    {
        parent::__construct('INVALID_SIGN ' . $message, self::INVALID_SIGN, $extra);
    }

}