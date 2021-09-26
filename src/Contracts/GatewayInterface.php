<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-22 22:09
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Contracts;

use Symfony\Component\HttpFoundation\Response;

interface GatewayInterface
{

    /**
     * Pay an order
     *
     * @param $endpoint
     * @param array $payload
     * @return Response
     */
    public function pay($endpoint, array $payload): Response;

}