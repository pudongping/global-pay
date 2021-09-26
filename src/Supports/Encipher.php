<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2021-08-23 17:32
 * E-mail: <276558492@qq.com>
 */
declare(strict_types=1);

namespace Pudongping\GlobalPay\Supports;

use Pudongping\GlobalPay\Exceptions\InvalidSignException;
use Pudongping\GlobalPay\Exceptions\InvalidConfigException;
use Pudongping\GlobalPay\Log;
use Illuminate\Support\Str;

class Encipher
{

    /**
     * 生成签名
     *
     * @param $data
     * @param $algorithm
     * @param $key
     * @return string|null
     * @throws InvalidSignException
     */
    public static function sign($data, $algorithm, $key)
    {
        unset($data['sign'], $data['sign_type']);

        // $query = urldecode(http_build_query(self::sort($data)));
        $query = self::getSignContent($data);

        if ($algorithm === 'MD5') {
            return self::signWithMD5($query, $key);
        }
        // 微信 md5 加密要把 key 拼接到最后
        if ($algorithm === 'md5') {
            $query .= '&key=';
            return self::signWithMD5($query, $key);
        }
        if ($algorithm === 'RSA' || $algorithm === '0001') {
            return self::signWithRSA($query, $key);
        }

        if ($algorithm === 'RSA2') {
            $data['sign_type'] = 'RSA2';
            // $query = urldecode(http_build_query(self::sort($data)));
            $query = self::getSignContent($data);
            return self::signWithRSA($query, $key, OPENSSL_ALGO_SHA256);
        }

        return null;
    }

    /**
     * 验证签名
     *
     * @param $data
     * @param $algorithm
     * @param $key
     * @return bool
     * @throws InvalidSignException
     */
    public static function verify($data, $algorithm, $key)
    {
        // GET 方式传参 ，php 会自动转义 + 号，想不出好的解决方法，只能粗暴点了
        $sign = str_replace(' ', '+', $data['sign']);
        unset($data['sign'], $data['sign_type']);
        // $query = urldecode(http_build_query(self::sort($data)));
        $query = self::getSignContent($data);

        if ($algorithm === 'MD5') {
            return hash_equals(self::signWithMD5($query, $key), $sign);
        }

        if ($algorithm === 'md5') {
            $query .= '&key=';
            $md5 = strtoupper(self::signWithMD5($query, $key));
            return hash_equals($md5, $sign);
        }

        if ($algorithm === 'RSA' || $algorithm === '0001') {
            return self::verifyWithRSA($query, $sign, $key);
        }

        if ($algorithm === 'RSA2') {
            return self::verifyWithRSA($query, $sign, $key, OPENSSL_ALGO_SHA256);
        }

        return false;
    }

    /**
     * @param $string
     * @param $key
     * @return string
     */
    public static function signWithMD5($string, $key)
    {
        return md5($string . $key);
    }

    /**
     * @param array $data
     * @return array
     */
    public static function sort(array $data)
    {
        ksort($data);
        reset($data);
        return $data;
    }

    /**
     * RSA 参数加密
     *
     * @param $data
     * @param $privateKey
     * @param int $signatureAlg
     * @return string
     * @throws InvalidSignException
     */
    public static function signWithRSA($data, $privateKey, $signatureAlg = OPENSSL_ALGO_SHA1)
    {
        if (! $privateKey) {
            throw new InvalidConfigException('Missing Alipay Config -- [private_key]');
        }

        if (Str::endsWith($privateKey, '.pem')) {
            $keyRes = openssl_pkey_get_private(
                Str::startsWith($privateKey, 'file://') ? $privateKey : 'file://' . $privateKey
            );
        } else {
            $keyRes = "-----BEGIN RSA PRIVATE KEY-----\n".
                wordwrap($privateKey, 64, "\n", true).
                "\n-----END RSA PRIVATE KEY-----";
        }

        if (empty($keyRes)) {
            throw new InvalidSignException('您使用的私钥格式错误，请检查 RSA 私钥配置', compact('privateKey'));
        }

        openssl_sign($data, $sign, $keyRes, $signatureAlg);

        $sign = base64_encode($sign);

        Log::debug('Alipay Generate Sign', ['original_params' => $data, 'generate_sign' => $sign]);

        if (is_resource($keyRes)) {
            openssl_free_key($keyRes);
        }

        return $sign;
    }

    /**
     * RSA 参数校验
     *
     * @param $data
     * @param $sign
     * @param $publicKey
     * @param int $signatureAlg
     * @return bool
     * @throws InvalidSignException
     */
    public static function verifyWithRSA($data, $sign, $publicKey, $signatureAlg = OPENSSL_ALGO_SHA1)
    {
        if (! $publicKey) {
            throw new InvalidConfigException('Missing Alipay Config -- [public_key]');
        }

        if (Str::endsWith($publicKey, '.crt')) {
            $keyRes = file_get_contents($publicKey);
        } elseif (Str::endsWith($publicKey, '.pem')) {
            $keyRes = openssl_pkey_get_public(
                Str::startsWith($publicKey, 'file://') ? $publicKey : 'file://' . $publicKey
            );
        } else {
            $keyRes = "-----BEGIN PUBLIC KEY-----\n".
                wordwrap($publicKey, 64, "\n", true).
                "\n-----END PUBLIC KEY-----";
        }

        if (empty($keyRes)) {
            throw new InvalidSignException('支付宝 RSA 公钥错误，请检查公钥文件格式是否正确', compact('data', 'sign'));
        }

        $isVerify = 1 ===openssl_verify($data, base64_decode($sign), $keyRes, $signatureAlg);

        if (is_resource($keyRes)) {
            openssl_free_key($keyRes);
        }

        return $isVerify;
    }

    /**
     * 拼接参数
     *
     * @param array $payload
     * @return string
     */
    public static function getSignContent(array $payload)
    {
        unset($payload['sign'], $payload['sign_type']);

        ksort($payload);

        $stringToBeSigned = '';

        foreach ($payload as $k => $v) {
            $stringToBeSigned .= $k . '=' . $v . '&';
        }

        return trim($stringToBeSigned, '&');
    }

}