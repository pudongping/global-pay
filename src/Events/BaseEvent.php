<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 21:07
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Events;

use Symfony\Contracts\EventDispatcher\Event;

class BaseEvent extends Event
{

    /**
     * Driver
     *
     * @var string
     */
    public $driver;

    /**
     * Method
     *
     * @var string
     */
    public $gateway;

    /**
     * Extra attributes
     *
     * @var mixed
     */
    public $attributes;

    /**
     * Bootstrap
     *
     * BaseEvent constructor.
     * @param string $driver
     * @param string $gateway
     */
    public function __construct(string $driver, string $gateway)
    {
        $this->driver = $driver;
        $this->gateway = $gateway;
    }

}