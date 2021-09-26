<?php
/**
 * 此事件将在请求支付方的 api 完成之后抛出。
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:11
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Events;

class ApiRequestedEvent extends BaseEvent
{

    /**
     * Endpoint
     *
     * @var string
     */
    public $endpoint;

    /**
     * Result
     *
     * @var array
     */
    public $result;

    /**
     * Bootstrap
     *
     * ApiRequestedEvent constructor.
     * @param string $driver  支付机构
     * @param string $gateway  支付网关
     * @param string $endpoint  支付的 url endpoint
     * @param array $result  请求后的返回数据
     */
    public function __construct(string $driver, string $gateway, string $endpoint, array $result)
    {
        $this->endpoint = $endpoint;
        $this->result = $result;

        parent::__construct($driver, $gateway);
    }

}