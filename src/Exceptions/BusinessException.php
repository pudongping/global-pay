<?php
/**
 * 业务相关报错
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 03:54
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Exceptions;

class BusinessException extends Exception
{

    public function __construct(string $message = '', $extra = [])
    {
        parent::__construct('ERROR_BUSINESS ' . $message, self::ERROR_BUSINESS, $extra);
    }

}