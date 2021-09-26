<?php
/**
 * 此事件将在所有参数处理完毕时抛出。
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:17
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Events;

class PayStartedEvent extends BaseEvent
{

    /**
     * Endpoint
     *
     * @var string
     */
    public $endpoint;

    /**
     * Payload
     *
     * @var array
     */
    public $payload;

    /**
     * Bootstrap
     *
     * PayStartedEvent constructor.
     * @param string $driver  支付机构
     * @param string $gateway  支付网关
     * @param string $endpoint  支付的 url endpoint
     * @param array $payload  数据
     */
    public function __construct(string $driver, string $gateway, string $endpoint, array $payload)
    {
        $this->endpoint = $endpoint;
        $this->payload = $payload;

        parent::__construct($driver, $gateway);
    }

}