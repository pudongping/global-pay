<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-23 15:01
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;

use Pudongping\GlobalPay\Contracts\GatewayInterface;
use Pudongping\GlobalPay\Exceptions\InvalidArgumentException;
use Pudongping\GlobalPay\Supports\Config;
use Pudongping\GlobalPay\Supports\Encipher;

abstract class GatewayAbstract implements GatewayInterface
{

    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * 签名助手
     *
     * @param array $payload
     * @param $algorithm
     * @param null $key
     * @return string|null
     * @throws Exceptions\InvalidSignException
     */
    public function sign(array $payload, $algorithm, $key = null)
    {
        if (null !== $key) {
            return Encipher::sign($payload, $algorithm, $key);
        }

        return Encipher::sign($payload, $algorithm, $this->config->get('private_key'));
    }

    /**
     * 签名验证
     *
     * @param $data
     * @param $algorithm
     * @param null $key
     * @return bool
     * @throws \Pudongping\GlobalPay\Exceptions\InvalidSignException
     */
    public function verify($data, $algorithm, $key = null)
    {
        if (null !== $key) {
            return Encipher::verify($data, $algorithm, $key);
        }

        return Encipher::verify($data, $algorithm, $this->config->get('private_key'));
    }

    /**
     * 获取花呗分期单通道支付相关参数
     *
     * @param array $payload
     * @return false|string
     * @throws InvalidArgumentException
     */
    public function getHbFqParam(array $payload)
    {
        if (! isset($payload['hb_fq_param'])) return false;

        // 花呗分期相关
        if (! isset($payload['hb_fq_param']['num'])) {
            throw new InvalidArgumentException('花呗分期缺乏必要参数 num');
        }

        $hbFqNum = $payload['hb_fq_param']['num'];
        if (! in_array((int)$hbFqNum, HbFqCost::$validateNper)) {
            throw new InvalidArgumentException('花呗分期参数 num 不合法');
        }

        // 跨境交易不允许自出资，因此 hb_fq_seller_percent 参数只能为 0，不能为 100
        // 这是因为，在跨境场景中，支付宝结算给商户的资金是外币，但花呗分期手
        // 续费则以人民币计算，若费用自出资，当涉及退款等场景时，费用的计算将无比
        // 复杂，因此在请求层面拦截了这种情况
        $isHasHousehold = $payload['hb_fq_param']['is_has_household'] ?? false;
        $isSellerPercent = $payload['hb_fq_param']['is_seller_percent'] ?? false;
        $hbFqSellerPercent = (string)HbFqCost::USER_ASSUME;
        if ($isHasHousehold && $isSellerPercent) {
            $hbFqSellerPercent = (string)HbFqCost::SELLER_ASSUME;
        }

        $data = [
            'hb_fq_num' => (string)$hbFqNum,  // 花呗分期数，仅支持传入3、6、12，其他期数暂不支持，传入会报错
            // 卖家承担收费比例，商家承担手续费传入 100，用户承担手续费传入 0，仅支持传入 100、0 两种，其他比例暂不支持，传入会报错。
            'hb_fq_seller_percent' => $hbFqSellerPercent
        ];

        return json_encode($data, 256);
    }

    /**
     * 花呗分期是否开启订单传参贴息活动
     *
     * @param array $payload
     * @return false|string
     * @throws InvalidArgumentException
     */
    public function getHbFqOrderSubsidy(array $payload)
    {
        if (! isset($payload['hb_fq_param'])) return false;

        // 花呗分期相关
        if (! isset($payload['hb_fq_param']['num'])) {
            throw new InvalidArgumentException('花呗分期缺乏必要参数 num');
        }

        $hbFqNum = $payload['hb_fq_param']['num'];
        if (! in_array((int)$hbFqNum, HbFqCost::$validateNper)) {
            throw new InvalidArgumentException('花呗分期参数 num 不合法');
        }

        $isOrderSubsidy = $payload['hb_fq_param']['is_order_subsidy'] ?? false;
        $subsidy = $isOrderSubsidy ? 'Y' : 'N';
        if (! $isOrderSubsidy) return false;

        $data = [
            'enable_thirdparty_subsidy' => $subsidy
        ];

        return json_encode($data, 256);
    }

}