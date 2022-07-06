<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-23 13:49
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

use Pudongping\GlobalPay\Supports\Config;
use Pudongping\GlobalPay\Gateways\Alipay;
use Pudongping\GlobalPay\Exceptions\InvalidArgumentException;
use GuzzleHttp\Client;

class Support
{

    protected $baseUri;

    protected $config;

    private static $instance;

    private function __construct(Config $config)
    {
        $this->baseUri = Alipay::$URL[$config->get('mode', Alipay::MODE_NORMAL)];
        $this->config = $config;
    }

    public static function create(Config $config)
    {
        if (('cli' === php_sapi_name()) || ! (self::$instance instanceof self)) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            throw new InvalidArgumentException('You should call [create] method first before using');
        }

        return self::$instance;
    }

    public function clear()
    {
        self::$instance = null;
    }

    public function getBaseUri()
    {
        return $this->baseUri;
    }

    public function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config->all();
        }

        if ($this->config->has($key)) {
            return $this->config[$key];
        }

        return $default;
    }

    public function httpClient()
    {
        $config = $this->config->get('http', [
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
        ]);
        // document link: https://guzzle-cn.readthedocs.io/zh_CN/latest/quickstart.html
        return new Client($config);
    }

}