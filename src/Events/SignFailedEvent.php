<?php
/**
 * 此事件将在签名验证失败时抛出。
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:21
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Events;

class SignFailedEvent extends BaseEvent
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
     * SignFailedEvent constructor.
     * @param string $driver  支付机构
     * @param string $gateway  支付网关
     * @param array $data  验签数据
     */
    public function __construct(string $driver, string $gateway, array $data)
    {
        $this->data = $data;

        parent::__construct($driver, $gateway);
    }

}