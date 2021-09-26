<?php
/**
 * 此事件将在收到支付方的请求（通常在异步通知或同步通知）时抛出。
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:20
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Events;

class RequestReceivedEvent extends BaseEvent
{

    /**
     * Received data
     *
     * @var array
     */
    public $data;

    /**
     * Bootstrap
     *
     * RequestReceivedEvent constructor.
     * @param string $driver  支付机构
     * @param string $gateway  支付网关
     * @param array $data  收到的数据
     */
    public function __construct(string $driver, string $gateway, array $data)
    {
        $this->data = $data;

        parent::__construct($driver, $gateway);
    }

}