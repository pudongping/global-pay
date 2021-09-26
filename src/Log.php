<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 18:34
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay;

use Pudongping\GlobalPay\Supports\Logger;

/**
 * @method static void emergency($message, array $context = array())
 * @method static void alert($message, array $context = array())
 * @method static void critical($message, array $context = array())
 * @method static void error($message, array $context = array())
 * @method static void warning($message, array $context = array())
 * @method static void notice($message, array $context = array())
 * @method static void info($message, array $context = array())
 * @method static void debug($message, array $context = array())
 * @method static void log($message, array $context = array())
 */
class Log extends Logger
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private static $instance;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    public static function setInstance(Logger $logger)
    {
        self::$instance = $logger;
    }

    public function __call($method, $args): void
    {
        call_user_func_array([self::getInstance(), $method], $args);
    }

    public static function __callStatic($method, $args): void
    {
        forward_static_call_array([self::getInstance(), $method], $args);
    }

}