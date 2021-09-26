<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-20 14:16
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay;

use Pudongping\GlobalPay\Supports\Config;
use Pudongping\GlobalPay\Supports\Logger;
use Pudongping\GlobalPay\Listeners\KernelLogSubscriber;
use Pudongping\GlobalPay\Contracts\GatewayApplicationInterface;
use Illuminate\Support\Str;
use Pudongping\GlobalPay\Exceptions\InvalidGatewayException;

/**
 * Class GlobalPay
 * @package Pudongping\GlobalPay
 * @method static alipay(array $config) 境外支付宝
 */
class GlobalPay
{

    /**
     * @var Config
     */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = new Config($config);

        $this->registerLogService();  // 注册日志服务
        $this->registerEventService();  // 注册事件服务

    }

    /**
     * @param $platform
     * @param $config
     * @return GatewayApplicationInterface
     * @throws InvalidGatewayException
     */
    public static function __callStatic($platform, $config): GatewayApplicationInterface
    {
        $app = new self(...$config);

        return $app->create($platform);
    }

    /**
     * @param string $platform
     * @return GatewayApplicationInterface
     * @throws InvalidGatewayException
     */
    protected function create(string $platform): GatewayApplicationInterface
    {
        $gateway = __NAMESPACE__ . '\\Gateways\\' . Str::studly($platform);

        if (class_exists($gateway)) {
            return self::make($gateway);
        }

        throw new InvalidGatewayException("Gateway [{$platform}] Not Exists!");

    }

    /**
     * @param string $gateway
     * @return GatewayApplicationInterface
     * @throws InvalidGatewayException
     */
    protected function make(string $gateway): GatewayApplicationInterface
    {
        $app = new $gateway($this->config);

        if ($app instanceof GatewayApplicationInterface) {
            return $app;
        }

        throw new InvalidGatewayException("Gateway [{$gateway}] Must Be An Instance Of GatewayApplicationInterface");

    }

    /**
     * 注册日志服务
     */
    protected function registerLogService()
    {
        $config = $this->config->get('log', []);
        $config['identify'] = $config['identify'] ?? 'pudongping.pay';

        $logger = new Logger();
        $logger->setConfig($config);

        Log::setInstance($logger);

    }

    /**
     * 注册事件服务
     */
    protected function registerEventService()
    {
        Events::setDispatcher(Events::createDispatcher());

        Events::addSubscriber(new KernelLogSubscriber());
    }

}