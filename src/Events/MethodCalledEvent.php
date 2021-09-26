<?php
/**
 * 此事件将在调用除 pay method 方法（例如，查询订单，退款，取消订单）时抛出。
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:15
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Events;

class MethodCalledEvent extends BaseEvent
{

    /**
     * endpoint
     *
     * @var string
     */
    public $endpoint;

    /**
     * payload
     *
     * @var array
     */
    public $payload;

    /**
     * Bootstrap
     *
     * MethodCalledEvent constructor.
     * @param string $driver  支付机构
     * @param string $gateway  调用方法
     * @param string $endpoint  支付的 url endpoint
     * @param array $payload  数据
     */
    public function __construct(string $driver, string $gateway, string $endpoint, array $payload = [])
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;

        parent::__construct($driver, $gateway);
    }

}