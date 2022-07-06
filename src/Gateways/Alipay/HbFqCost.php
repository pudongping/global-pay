<?php
/**
 * document link： https://opendocs.alipay.com/mini/introduce/antcreditpay-istallment
 * document link： https://opendocs.alipay.com/open/277/105952
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-29 16:03
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Gateways\Alipay;


class HbFqCost
{

    const USER_ASSUME = 0;  // 用户承担手续费
    const SELLER_ASSUME = 100;  // 商家承担手续费

    public static $validateNper = [3, 6, 12];  // 花呗分期合法的分期数

    /**
     * 支付宝花呗分期，默认分期费率（如果支付宝做活动时，可能会有变化）
     *
     * @var \float[][]
     */
    public static $rate = [
        self::USER_ASSUME => [
            3 => 0.023,
            6 => 0.045,
            12 => 0.075
        ],
        self::SELLER_ASSUME => [
            3 => 0.018,
            6 => 0.045,
            12 => 0.075
        ],
    ];

    /**
     * 获取花呗分期计费情况
     *
     * @param float $totalAmount  本金
     * @param bool $isShowAll  是否显示每一期的还款数
     * @param bool $isSellerPercent  是否商家承担所有的手续费
     * @param array $customerRates  用户自定义分期费率
     * @return array
     */
    public function fetchHbFqCost(float $totalAmount, bool $isShowAll = false, bool $isSellerPercent = false, array $customerRates = []): array
    {
        $assume = $isSellerPercent ? self::SELLER_ASSUME : self::USER_ASSUME;
        $realRates = $customerRates ?: self::$rate;  // 优先使用用户传入的动态分期费率
        $rates = $realRates[$assume];
        $data = [];

        foreach ($rates as $nper => $rate) {
            $data[] = $this->calHbFqCost($nper, $rate, $totalAmount, $isShowAll);
        }

        return $data;
    }

    /**
     * 计算花呗分期手续费
     *
     * @param int $nper  期数
     * @param float $rate  费率
     * @param float $totalAmount  本金
     * @param bool $showAll  是否显示每一期的还款数
     * @return array
     */
    public function calHbFqCost(int $nper, float $rate, float $totalAmount, bool $showAll = false)
    {
        $totalAmountCent = bcmul((string)$totalAmount, '100', 4);  // 1. 把金额单位转化成分 cent
        // 用户每期本金
        $perAmount = floor(floatval(bcdiv($totalAmountCent, (string)$nper, 4)));  // 2. 计算每期本金（用总金额/总期数，结果以分表示，向下取整）

        // 用户每期手续费
        $buyerTotalCost = (float)bcmul($totalAmountCent, (string)$rate, 4);  //  2. 用转化为分后的金额乘以买家费率，得到以分表示的买家总费用（总手续费）
        $roundTotalCost = round($buyerTotalCost, 0, PHP_ROUND_HALF_EVEN);  // 3. 对费用进行取整（取整规则为 ROUND_HALF_EVEN ）
        $perCharge = floor(floatval(bcdiv((string)$roundTotalCost, (string)$nper, 4)));  // 4. 计算每期费用（用总费用/总期数，结果以分表示，向下取整）

        // 用户每期总费用
        $perTotalAmount = bcadd((string)$perAmount, (string)$perCharge);

        // 金额以 [元] 为单位
        $perAmountYuan = floatval(bcdiv((string)$perAmount, '100', 2));
        $perChargeYuan = floatval(bcdiv((string)$perCharge, '100', 2));
        $perTotalAmountYuan = floatval(bcdiv((string)$perTotalAmount, '100', 2));
        $buyerTotalCostYuan = round(floatval(bcdiv((string)$buyerTotalCost, '100', 4)), 2);  // 花呗分期的总手续费实行“四舍五入”的原则进行计算

        $ret = [
            'nper' => $nper,  // 期数
            'total_amount' => $totalAmount,  // 本金
            'total_charge' => $buyerTotalCostYuan,  // 总手续费
            'rate' => $rate,  // 利率
            'per_charge' => $perChargeYuan,  // 每期手续费
            'per_amount' => $perAmountYuan,  // 每期本金
            'per_total_amount' => $perTotalAmountYuan,  // 每期总费用
            'refund_list' => [],  // 还款列表
        ];

        if ($showAll) {
            $ret['refund_list'] = $this->getRefundList($ret);
        }

        return $ret;
    }

    /**
     * 获取还款的列表
     *
     * @param array $params
     * @return array
     */
    public function getRefundList(array $params)
    {
        $nper = $params['nper'];  // 期数

        $data = [];
        for ($i = 1; $i <= $nper; $i++) {
            $item = [];
            $item['nper'] = $i;  // 第几期
            $item['charge'] = $params['per_charge'];  // 当前期数所需要支付的手续费
            $item['amount'] = $params['per_amount'];  // 当前期数所需要支付的本金数
            $item['current_total_amount'] = $params['per_total_amount'];  // 当前期数所需要支付的总费用
            $data[] = $item;
        }

        $charges = array_column($data, 'charge');
        // 计算的所有手续费总和
        $chargesSum = array_reduce($charges, function ($carry, $item) {
            return bcadd((string)$carry, (string)$item, 2);
        }, '0');

        $amounts = array_column($data, 'amount');
        // 计算的所有本金总和
        $amountsSum = array_reduce($amounts, function ($carry, $item) {
            return bcadd((string)$carry, (string)$item, 2);
        }, '0');

        // 如果所需支付的总手续费大于计算后的手续费总和，那么则需要将缺少的手续费补加到第一期
        if ($params['total_charge'] > (float)$chargesSum) {
            $data[0]['charge'] = floatval(bcadd(strval($data[0]['charge']), bcsub(strval($params['total_charge']), strval($chargesSum), 2), 2));
        }
        // 如果所需要支付的本金大于计算后的本金总和，那么则需要将缺少的本金补加到第一期
        if ($params['total_amount'] > (float)$amountsSum) {
            $data[0]['amount'] = floatval(bcadd(strval($data[0]['amount']), bcsub(strval($params['total_amount']), strval($amountsSum), 2), 2));
        }
        // 第一期所需要支付的总金额
        $data[0]['current_total_amount'] = floatval(bcadd(strval($data[0]['charge']), strval($data[0]['amount']), 2));

        return $data;
    }

}