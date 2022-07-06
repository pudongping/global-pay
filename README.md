
<h1 align="center">GlobalPay</h1>

支持国际版支付的 PHP SDK，目前**只支持支付宝国际版**。因目前支付宝跨境在线支付服务只支持 app、wap、web 和报关这四种，本 SDK 提供了 app、wap、web 这三种跨境支付，[详见支付宝国际支付官方文档](https://global.alipay.com/docs/ac/legacy/legacydoc) 。

> 创建本仓库的初衷主要在于，本人目前所在单位有跨境支付的需求，之前境内支付一直采用 [yansongda/pay](https://github.com/yansongda/pay) 扩展包，也习惯了这种调用方式，但由于贵包暂时不支持境外支付，因此只能撸起袖子自己写一个了。
> 本只想此扩展包供团队内部使用，但近期刚好也有朋友问我境外支付采用的什么扩展包，遂想着将此包开源出去，以供各位同学参考！如果发现此包有 bug，欢迎随时提 PR，希望各位同学使用愉快！  
> 本扩展包里面的功能只打算以本人所在单位的需求为导向进行扩展，因此暂时只考虑跨境支付宝支付，其他暂且不考虑。

## 安装

```shell
composer require pudongping/global-pay -vvv
```

## 详细文档地址

[https://pudongping.github.io/global-pay-doc](https://pudongping.github.io/global-pay-doc)

## 特点

- 命名规范
- 隐藏开发者不需要关注的细枝末节
- 符合 PSR 规范，可以方便的与各种 PHP 框架集成
- 使用方式简单，使用过 [yansongda/pay](https://github.com/yansongda/pay) 扩展包的用户可无缝上手

## 运行环境

- PHP >= 7.1.3
- Composer

## 支持的支付方法

- 境外支付宝电脑支付
- 境外支付宝手机网站支付
- 境外支付宝 APP 支付

方法 | 描述
:---: | :---:
web | 电脑支付
wap | 手机网站支付
app | APP 支付

## 所有支付方法都支持的方法

- find(array|string $order)  
**说明：** 查找订单接口  
**参数：** `$order` 为 `string` 类型时，请传入系统订单号，对应跨境支付宝中的 `out_trade_no` 参数； `array` 类型时，参数请参考[支付宝境外订单单笔查询文档](https://global.alipay.com/docs/ac/global/single_trade_query_cn) 。    
**返回：** 查询成功，返回 `Illuminate\Support\Collection` 实例，可以通过 `$collection->toArray()` 或者 `$collection->all()` 或者 `$collection->get('field')` 访问服务器返回的数据。  

- refund(array $order)   
**说明：** 退款接口  
**参数：**  `$order` 数组格式，退款参数请参考[支付宝境外退款接口文档](https://global.alipay.com/docs/ac/global/forex_refund_cn) 。   
**返回：** 退款成功，返回 `Illuminate\Support\Collection` 实例，可以通过 `$collection->toArray()` 或者 `$collection->all()` 或者 `$collection->get('field')` 访问服务器返回的数据。  
  
- verify()  
**说明：** 验证服务器返回数据是否合法  
**返回：** 验证成功，返回 `Illuminate\Support\Collection` 实例，可以通过 `$collection->toArray()` 或者 `$collection->all()` 或者 `$collection->get('field')` 访问服务器返回的数据。  
  
## 其他通用方法  

- getExchangeRate()  
**说明：** 获取汇率。详见[支付宝境外汇率查询接口](https://global.alipay.com/docs/ac/global/forex_rate_file_cn) 。  
**返回：** 获取成功，返回 `Illuminate\Support\Collection` 实例，可以通过 `$collection->toArray()` 或者 `$collection->all()` 或者 `$collection->get('field')` 访问服务器返回的数据。  
**注意：** 1、货币间的汇率会在北京时间每日 9：00 到 11:00 间变动一次；  2、汇率每日获取上限为 100 次。 （可能需要考虑通过缓存保存汇率，防止接口出现异常，因为本 SDK 没有做缓存处理）
  
- getHbFqCost(float $totalAmount, bool $isShowAll = false, bool $isSellerPercent = false)  
**说明：** 获取花呗分期计费情况  
**参数：** `$totalAmount` 为分期的本金，`$isShowAll` 为是否显示每一期的还款数，`$isSellerPercent` 为 `true` 表示商家承担全部手续费，为 `false` 表示用户承担全部手续费。  
**返回：** 获取成功，返回 `Illuminate\Support\Collection` 实例，可以通过 `$collection->toArray()` 或者 `$collection->all()` 或者 `$collection->get('field')` 访问服务器返回的数据。

返回参数说明

参数 | 含义
--- | :---:
nper | 期数
total_amount | 本金
total_charge | 总手续费
rate | 利率
per_charge | 每期手续费
per_amount | 每期本金
per_total_amount | 每期总费用
refund_list | 还款列表
refund_list.nper | 第几期
refund_list.charge | 当前期数所需要支付的手续费
refund_list.amount | 当前期数所需要支付的本金数
refund_list.current_total_amount | 当前期数所需要支付的总费用

## 使用说明

### 非花呗分期支付

```php

<?php

declare(strict_types=1);

namespace App\Controller;

use Pudongping\GlobalPay\GlobalPay;
use Pudongping\GlobalPay\Log;

class PayController
{

    protected $config = [
        'partner' => '2088000000000000',  // 合作身份者 id，以 2088 开头的 16 位纯数字
        'notify_url' => 'http://a90b-8-37-43-168.demo.io/index/notify_url',  // 异步回调地址
        'return_url' => 'http://a90b-8-37-43-168.demo.io/index/return_url',  // 同步回调地址
        'refer_url' => 'https://www.demo.net',  // 二级商户网站地址
        'seller_email' => 'xxxx@gmail.com',  // 签约支付宝账号或卖家支付宝帐户
        'key' => 'xxxx',  // 安全检验码，以数字和字母组成的 32 位字符
        'sign_type' => 'RSA',  // 不需要修改
        'input_charset' => 'UTF-8',  // 商户网站使用的编码格式，建议不需要修改
        'transport' => 'http',  // 访问模式,根据自己的服务器是否支持 ssl 访问，若支持请选择 https；若不支持请选择 http
        'split_fund' => '2088000000000000:0.10',  // 接受分账资金的支付宝账户 ID 和比例，用逗号分隔其他帐号信息。ID 是以 2088 开头的纯 16 位数字。
        'private_key' => '/Users/pudongping/glory/key/alipay_private_key.pem',  // 私钥路径
        'public_key' => '/Users/pudongping/glory/key/alipay_public_key.pem',  // 公钥路径

        'log' => [ // optional
            'file' => 'alipay.log',  // 当前目录下
            'level' => 'debug', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ];

    /**
     * document link: https://global.alipay.com/docs/ac/web_cn/about
     *
     * @return mixed
     */
    public function web()
    {
        $order = [
            'out_trade_no' => time(),
            'subject' => '交易 alex ',
            'currency' => 'JPY',
            'rmb_fee' => '0.20',
            'trade_information' => json_encode([
                'business_type' => 4,
                'goods_info' => '変身ベ...^1',
                'total_quantity' => 1
            ], 256),
            // '_only_args' => true  // 只需要返回参数模式时增加
        ];

        $globalPay = GlobalPay::alipay($this->config)->web($order);

        return $globalPay->send();

        // 如果设置了 `_only_args` 为 true，则使用以下方法获取所有的参数
        // var_dump($globalPay->getContent());

    }

    /**
     * document link:  https://global.alipay.com/docs/ac/wap_cn/start
     *
     * @return mixed
     */
    public function wap()
    {

        $order = [
            'out_trade_no' => time(),
            'subject' => '交易 alex ',
            'currency' => 'JPY',
            'rmb_fee' => '0.10',
            'trade_information' => json_encode([
                'business_type' => 4,
                'goods_info' => '変身ベ...^1',
                'total_quantity' => 1
            ], 256),
            // '_only_args' => true  // 只需要返回参数模式时增加
        ];

        $globalPay = GlobalPay::alipay($this->config)->wap($order);

        return $globalPay->send();

        // 如果设置了 `_only_args` 为 true，则使用以下方法获取所有的参数
        // var_dump($globalPay->getContent());

    }

    /**
     * document link: https://global.alipay.com/docs/ac/app_cn/about
     */
    public function app()
    {
        $order = [
            'out_trade_no' => 'alex_' . time(),
            'subject' => '交易 5200',
            'currency' => 'JPY',
            'rmb_fee' => '1.01',
            'trade_information' => json_encode([
                'business_type' => 4,
                'goods_info' => '大海にて^1',
                'total_quantity' => 1
            ], 256),
        ];

        $globalPay = GlobalPay::alipay($this->config)->app($order);

        $content = $globalPay->getContent();

        var_dump($content);

    }

    /**
     * 单笔查询接口 document link: https://global.alipay.com/docs/ac/global/single_trade_query_cn
     */
    public function find()
    {
        // out_trade_no 和 trade_no 参数可以同时含有，也可以二选一
        $order = [
            'out_trade_no' => 'alex_1629950066',
            // 'trade_no' => '2021082622001364941434754996',
        ];

        $globalPay = GlobalPay::alipay($this->config)->find($order);

        var_dump($globalPay->toArray());

    }

    /**
     * 退款接口 document link： https://global.alipay.com/docs/ac/global/forex_refund_cn
     */
    public function refund()
    {
        $order = [
            'out_return_no' => 'alex_refund_' . time(),
            'out_trade_no' => 'alex_1629950066',
            'return_rmb_amount' => '1.01',
            'currency' => 'JPY',
            'reason' => '退款测试',
            // 'is_sync' => 'N',  // 如果 is_sync => N 则开启异步通知，否则不开启异步通知，不开启异步通知 notify_url 参数将会失效（不需要开启时，则不需要设置）
            // 'notify_url' => 'http://api.demo.com:8016/v2/alipay/forexNotify',  // $order['notify_url'] 设置了，则使用 $order['notify_url'] 的值，否则使用配置文件中的 notify_url 参数
            // 'type' => 'pc',  // 如果是网站支付，则需要设置 type 参数为 pc，手机浏览器或支付宝钱包支付时，不需要设置
        ];

        $globalPay = GlobalPay::alipay($this->config)->refund($order);

        var_dump($globalPay->toArray());

    }

    /**
     * 同步验签
     */
    public function return()
    {
        $data = GlobalPay::alipay($this->config)->verify();
    }

    /**
     * 异步验签
     *
     * @return mixed
     */
    public function notify()
    {
        $globalPay = GlobalPay::alipay($this->config);

        try {

            $data = $globalPay->verify();  // 验签

            // 建议必须对以下几个参数进行业务逻辑验证
            $outTradeNo = $data->get('out_trade_no');  // 商户需要验证该通知数据中的 out_trade_no 是否为商户系统中创建的订单号。
            $tradeStatus = $data->get('trade_status');  // 在支付宝的业务通知中，只有交易通知状态为 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            $totalFee = $data->get('total_fee');  // 该笔订单的总金额。请求时对应的参数，原样通知回来。（外币金额）

            Log::debug('GlobalPay Notify ===> ', $data->all());

        } catch (\Exception $exception) {
            Log::error('异步通知异常 ===> ' . $exception->getMessage());
            return $globalPay->fail()->send();  // 其他框架
            // return $globalPay->fail();  // Laravel 框架可以直接这样
        }

        return $globalPay->success()->send();  // 其他框架
        // return $globalPay->success();  //  Laravel 框架可以直接这样
    }

    /**
     * 获取汇率
     */
    public function getExchangeRate()
    {
        $globalPay = GlobalPay::alipay($this->config)->getExchangeRate();

        var_dump($globalPay->toArray());
    }

}

```

### 花呗分期支付

```php

<?php

declare(strict_types=1);

namespace App\Controller;

use Pudongping\GlobalPay\GlobalPay;
use Pudongping\GlobalPay\Log;

class HbfqPayController
{

    public function web()
    {
        $order = [
            'out_trade_no' => time(),
            'subject' => '交易 alex',
            'currency' => 'JPY',
            'rmb_fee' => 5.45,
            'trade_information' => json_encode([
                'business_type' => 4,
                'goods_info' => '交易费用^1',
                'total_quantity' => 1
            ], 256),
            'hb_fq_param' => [
                'num' => 3,  // 花呗分期分期数，只支持 3、6、12 期
                // 只有 is_has_household 为 true， is_seller_percent 才能设置为 true
                'is_has_household' => false,  // 是否拥有出资户，只有拥有出资户，商家才能贴息，否则只能用户贴息
                'is_seller_percent' => false,  // 是否商家贴息
                // 花呗分期开启订单传参贴息活动（不支持 PC 支付，无论是国际还是国内的交易都不支持）
                // 因此相比 app 支付，不能传递 is_order_subsidy 参数
            ],
            // '_only_args' => true  // 只需要返回参数模式时增加
        ];

        $globalPay = GlobalPay::alipay($this->config)->web($order);

        return $globalPay->send();

        // 如果设置了 `_only_args` 为 true，则使用以下方法获取所有的参数
        // var_dump($globalPay->getContent());
    }

    public function wap()
    {
        $order = [
            'out_trade_no' => time(),
            'subject' => '交易 alex',
            'currency' => 'JPY',
            'rmb_fee' => 5.45,
            'trade_information' => json_encode([
                'business_type' => 4,
                'goods_info' => '交易费用^1',
                'total_quantity' => 1
            ], 256),
            'hb_fq_param' => [
                'num' => 3,  // 花呗分期分期数，只支持 3、6、12 期
                // 只有 is_has_household 为 true， is_seller_percent 才能设置为 true
                'is_has_household' => false,  // 是否拥有出资户，只有拥有出资户，商家才能贴息，否则只能用户贴息
                'is_seller_percent' => false,  // 是否商家贴息
                // 花呗分期开启订单传参贴息活动（不支持 PC 支付，无论是国际还是国内的交易都不支持）
                // 因此相比 app 支付，不能传递 is_order_subsidy 参数
            ],
            // '_only_args' => true  // 只需要返回参数模式时增加
        ];

        $globalPay = GlobalPay::alipay($this->config)->wap($order);

        return $globalPay->send();

        // 如果设置了 `_only_args` 为 true，则使用以下方法获取所有的参数
        // var_dump($globalPay->getContent());
    }

    public function app()
    {
        $order = [
            'out_trade_no' => time(),
            'subject' => '交易 alex',
            'currency' => 'JPY',
            'rmb_fee' => 3.45,
            'trade_information' => json_encode([
                'business_type' => 4,
                'goods_info' => '交易费用^1',
                'total_quantity' => 1
            ], 256),
            'hb_fq_param' => [
                'num' => 3,  // 花呗分期分期数，只支持 3、6、12 期
                // 只有 is_has_household 为 true， is_seller_percent 才能设置为 true，否则 is_seller_percent 只能设置为 false
                'is_has_household' => false,  // 是否拥有出资户，只有拥有出资户，商家才能贴息，否则只能用户贴息
                'is_seller_percent' => false,  // 是否商家贴息， true 为商家贴息， false 为用户贴息
                'is_order_subsidy' => false,  // 是否开启订单传参贴息活动
                // 出资户贴息和订单传参贴息只能允许一个为 true
            ],
        ];

        $globalPay = GlobalPay::alipay($this->config)->app($order);

        $content = $globalPay->getContent();

        var_dump($content);
    }

    public function find()
    {
        // out_trade_no 和 trade_no 参数可以同时含有，也可以二选一
        $order = [
            'out_trade_no' => 'alex_1629950066',
            // 'trade_no' => '2021082622001364941434754996',
            'is_hbfq' => true,  // 该笔订单是否为花呗分期支付，订单查询出来的结果会含有 hb_fq_num 参数，不是花呗分期订单则没有这个参数
        ];

        $globalPay = GlobalPay::alipay($this->config)->find($order);

        var_dump($globalPay->toArray());
    }

    public function refund()
    {
        // 花呗分期退款和非花呗分期退款操作流程一致
        $order = [
            'out_return_no' => 'alex_refund_' . time(),
            'out_trade_no' => 'alex_1629950066',
            'return_rmb_amount' => 3.45,
            'currency' => 'JPY',
            'reason' => '退款测试',
            // 'is_sync' => 'N',  // 如果 is_sync => N 则开启异步通知，否则不开启异步通知，不开启异步通知 notify_url 参数将会失效（不需要开启时，则不需要设置）
            // 'notify_url' => 'http://api.demo.com:8016/v2/alipay/forexNotify',  // $order['notify_url'] 设置了，则使用 $order['notify_url'] 的值，否则使用配置文件中的 notify_url 参数
            // 'type' => 'pc',  // 如果是网站支付，则需要设置 type 参数为 pc，手机浏览器或支付宝钱包支付时，不需要设置
        ];

        $globalPay = GlobalPay::alipay($this->config)->refund($order);

        var_dump($globalPay->toArray());
    }

    public function return()
    {
        $data = GlobalPay::alipay($this->config)->verify();
    }

    public function notify()
    {
        $globalPay = GlobalPay::alipay($this->config);

        try {

            $data = $globalPay->verify();  // 验签

            // 建议必须对以下几个参数进行业务逻辑验证
            $outTradeNo = $data->get('out_trade_no');  // 商户需要验证该通知数据中的 out_trade_no 是否为商户系统中创建的订单号。
            $tradeStatus = $data->get('trade_status');  // 在支付宝的业务通知中，只有交易通知状态为 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            $totalFee = $data->get('total_fee');  // 该笔订单的总金额。请求时对应的参数，原样通知回来。（外币金额）

            Log::debug('GlobalPay Notify ===> ', $data->all());

        } catch (\Exception $exception) {
            Log::error('异步通知异常 ===> ' . $exception->getMessage());
            return $globalPay->fail()->send();  // 其他框架
            // return $globalPay->fail();  // Laravel 框架可以直接这样
        }

        return $globalPay->success()->send();  // 其他框架
        // return $globalPay->success();  //  Laravel 框架可以直接这样
    }

    /**
     * 获取花呗分期计费情况
     */
    public function getHbFqCost()
    {
        $totalAmount = 100.88;

        // 只需要获取 3 6 12 期相对应到还款数
        // $globalPay = GlobalPay::alipay($this->config)->getHbFqCost($totalAmount);

        // 获取 3 6 12 期相对应到还款数且显示出每一期的还款情况（用户承担所有的手续费）
        // $globalPay = GlobalPay::alipay($this->config)->getHbFqCost($totalAmount, true);

        // 获取 3 6 12 期相对应到还款数且显示出每一期的还款情况（商家承担所有的手续费）
        // $globalPay = GlobalPay::alipay($this->config)->getHbFqCost($totalAmount, true, true);

        // 花呗分期计算费率，参见：https://opendocs.alipay.com/open/277/105952?ref=api
        $customerRates = [
            0 => [  // 用户承担手续费
                3 => 0.023,
                6 => 0.045,
                12 => 0.075
            ],
            100 => [  // 商家承担手续费
                3 => 0.018,
                6 => 0.045,
                12 => 0.075
            ]
        ];
        // 允许自己传入花呗分期费率（支付宝做活动时，费率可能会有变化）
        $globalPay = GlobalPay::alipay($this->config)->getHbFqCost($totalAmount, true, false, $customerRates);
        
        var_dump($globalPay->toArray());
    }

}

```

## 致谢

本仓库架构思想借鉴于 「 [yansongda/pay](https://github.com/yansongda/pay) 」，感谢贵包作者提供如此好用的 PHP 支付 SDK 扩展包！

## LICENSE
MIT