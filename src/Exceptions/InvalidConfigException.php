<?php
/**
 * 配置相关错误
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 22:33
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Exceptions;

class InvalidConfigException extends Exception
{

    public function __construct(string $message = '', $extra = null)
    {
        parent::__construct('INVALID_CONFIG ' . $message, self::INVALID_CONFIG, $extra);
    }

}