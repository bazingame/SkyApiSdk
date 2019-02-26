<?php
/**
 * Author: Howard
 * CreateTime: 19-2-26 下午7:18
 * Description: SkyApi类，自动获取\刷新token写入引入此类的文件所在目录下的token.json文件，可自行重构为数据库存储方式。
 *              本类另外提供获取开发者信息、获取用户课程表表两个样例方法，开发者可以实例化后自行开发所需功能，也可在该类下继续实现。
 */
include_once "Curl.php";

class SkyApi
{
    public $curl = null;
    private $token = '';
    private $devEmail = '';
    private $devSecret = '';

    public function __construct($devEmail, $devSecret)
    {
        $this->devEmail = $devEmail;
        $this->devSecret = $devSecret;
        $this->curl = Curl::getInstance();
        $this->getToken();
        $this->setToken();
    }

    /**
     * 获取token
     */
    public function getToken()
    {
        $res = file_get_contents('./token.json');
        $result = json_decode($res, true);
        $issued_at = $result["issued_at"];   //token签发时间
        $token = $result["token"];
        $expires_in = $result["expires_in"];     //有效时间
        if (time() >= ($issued_at + $expires_in) || $token == "") {
            $url = APIBASE . '/auth/token';
            $res = $this->curl->get($url, array('email' => $this->devEmail, 'password' => $this->devSecret));
            $resArr = json_decode($res, true);
            $token = $resArr["token"];
            file_put_contents('./token.json', $res);
        }
        $this->token = $token;
        return $token;
    }

    /**
     * 请求头设置token
     */
    public function setToken($token = '')
    {
        $this->curl->setHeader(['Authorization:Bearer ' . $this->token]);
    }

    /**
     * 登录通行证
     */
    public function loginPassport($type = 'sid', $sid = '', $password = '')
    {
        $url = APIBASE . '/passport/login/';
        $res = $this->curl->post($url, ['type' => $type, 'sid' => $sid, 'password' => $password]);
        return $res;
    }

    /**
     * 获取开发者信息
     */
    public function getDevSelfInfo()
    {
        $url = APIBASE . '/perm/user/' . $this->devEmail;
        $res = $this->curl->get($url);
        return $res;
    }

    /**
     * 获取用户课程
     */
    public function getCourse()
    {
        $url = APIBASE . '/edu/course';
        $res = $this->curl->get($url);
        return $res;
    }

}

