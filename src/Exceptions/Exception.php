<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-20 14:52
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Exceptions;

use Throwable;

class Exception extends \Exception
{

    /**
     * 未知错误
     */
    public const UNKNOWN_ERROR = 9999;

    /**
     * 参数不合法
     */
    public const INVALID_ARGUMENT = 10001;

    /**
     * 业务相关报错
     */
    public const ERROR_BUSINESS = 10002;

    /**
     * 请求报错
     */
    public const ERROR_HTTP = 10003;

    /**
     * 无效网关错误
     */
    public const INVALID_GATEWAY = 10004;

    /**
     * 配置相关错误
     */
    public const INVALID_CONFIG = 10005;

    /**
     * 签名无效错误
     */
    public const INVALID_SIGN = 10006;

    /**
     * raw
     *
     * @var null
     */
    public $extra = null;

    public function __construct(string $message = '', int $code = self::UNKNOWN_ERROR, $extra = null, Throwable $previous = null)
    {
        $message = ('' === $message) ? 'Unknown Error' : $message;
        $this->extra = is_array($extra) ? $extra : [$extra];

        parent::__construct($message, $code, $previous);
    }

}