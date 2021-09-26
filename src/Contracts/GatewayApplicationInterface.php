<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 22:00
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Contracts;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Collection;

interface GatewayApplicationInterface
{

    /**
     * To pay
     *
     * @param string $gateway
     * @param array $params
     * @return Response
     */
    public function pay($gateway, $params);

    /**
     * Query an order
     *
     * @param string|array $order
     * @return Collection
     */
    public function find($order);

    /**
     * Refund an order
     *
     * @param array $order
     * @return Collection
     */
    public function refund(array $order);

    /**
     * Verify a request
     *
     * @param array|null $content
     * @return Collection
     */
    public function verify($content);

    /**
     * Echo success to server
     *
     * @return Response
     */
    public function success();

}