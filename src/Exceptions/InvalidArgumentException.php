<?php
/**
 * 参数无效相关错误
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-20 15:57
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Exceptions;

class InvalidArgumentException extends Exception
{

    public function __construct(string $message = '', $extra = null)
    {
        parent::__construct('INVALID_ARGUMENT ' . $message, self::INVALID_ARGUMENT, $extra);
    }

}