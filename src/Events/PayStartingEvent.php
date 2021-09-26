<?php
/**
 * 此事件将在最开始进行支付时进行抛出。此时 SDK 只进行了相关初始化操作，其它所有操作均未开始。
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:18
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Events;

class PayStartingEvent extends BaseEvent
{

    /**
     * 传递的原始参数
     *
     * @var array
     */
    public $params;

    /**
     * Bootstrap
     *
     * PayStartingEvent constructor.
     * @param string $driver  支付机构
     * @param string $gateway  支付网关
     * @param array $params  传递的原始参数
     */
    public function __construct(string $driver, string $gateway, array $params)
    {
        $this->params = $params;

        parent::__construct($driver, $gateway);
    }

}