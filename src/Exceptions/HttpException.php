<?php
/**
 * http 请求相关错误
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-20 15:58
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Exceptions;

class HttpException extends Exception
{

    public function __construct(string $message = '', $extra = null)
    {
        parent::__construct('ERROR_HTTP ' . $message, self::ERROR_HTTP, $extra);
    }

}