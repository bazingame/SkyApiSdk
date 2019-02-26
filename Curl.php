<?php
/**
 * Author: Howard
 * CreateTime: 19-2-26 下午8:20
 * Description: 封装了GET、POST、PUT、PATCH、DELETE方法的curl操作类，使用单例模式，支持链式操作,
 */

class Curl
{
    //单例
    private static $instance = null;
    //curl句柄
    private static $ch = null;
    //默认配置
    private static $defaults = array();
    //配置条件
    private static $options = array();
    //http状态码
    private static $httpCode = 0;
    //请求结果
    private static $response = null;
    //CURL信息
    private static $curlInfo = array();
    //头信息
    private static $header = null;

    /**
     *  构造函数
     **/
    private function __construct($options)
    {
        //默认配置在此设置
        self::$defaults['CURLOPT_CONNECTTIMEOUT'] = 30;
        self::$defaults['CURLOPT_HEADER'] = 0;
        self::$defaults['CURLOPT_RETURNTRANSFER'] = 1;
        self::$defaults['CURLOPT_HTTPHEADER'] = ['Content-Type:application/json'];
        self::$defaults['CURLINFO_HEADER_OUT'] = 1;

        //合并配置参数
        self::$options = array_merge(self::$defaults, $options);
        //初始化
        self::init();
    }

    /**
     *  单例模式
     **/
    public static function getInstance($options = array())
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    /**
     *  防止克隆对象
     **/
    private function __clone()
    {
        //防止clone函数克隆对象，破坏单例模式
    }

    /**
     *  初始化，开启句柄
     **/
    private function init()
    {
        self::$ch = curl_init();
        foreach (self::$options as $k => $v) {
            $options[constant($k)] = $v;
        }
        curl_setopt_array(self::$ch, $options);    //批量配置设置
    }

    /**
     *  发送请求，获取报文
     **/
    private function request($url)
    {
        //https请求跳过证书检查
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
        if ($SSL) {
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        self::$response = self::toUtf8(curl_exec(self::$ch));
        if (curl_errno(self::$ch)) {
            self::sendError(curl_error(self::$ch));
            return false;
        }
        if (self::$options['CURLOPT_HEADER']) {    //开启头信息
            $headerSize = curl_getinfo(self::$ch, CURLINFO_HEADER_SIZE);
            self::$header = substr(self::$response, 0, $headerSize);    //存储头信息
            return substr(self::$response, $headerSize);    //返回body
        }
        return self::$response;
    }

    /**
     *  GET操作
     **/
    public function get($url, $query = '')
    {
        if (!empty($query)) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= is_array($query) ? http_build_query($query) : $query;
        }

        curl_setopt(self::$ch, CURLOPT_HTTPGET, 1);    //GET
        curl_setopt(self::$ch, CURLOPT_URL, $url);

        return self::request($url);
    }

    /**
     *  POST操作
     **/
    public function post($url, $query = '')
    {
        curl_setopt(self::$ch, CURLOPT_POST, 1);    //POST
        curl_setopt(self::$ch, CURLOPT_URL, $url);
        curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
        return self::request($url);
    }

    /**
     *  PUT操作
     */
    public function put($url,$query = ''){
        curl_setopt(self::$ch,CURLOPT_CUSTOMREQUEST,"PUT");
        curl_setopt(self::$ch, CURLOPT_URL, $url);
        curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
        return self::request($url);
    }

    /**
     *  PATCH操作
     */
    public function patch($url,$query = ''){
        curl_setopt(self::$ch,CURLOPT_CUSTOMREQUEST,"PATCH");
        curl_setopt(self::$ch, CURLOPT_URL, $url);
        curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
        return self::request($url);
    }

    /**
     *  DELETE操作
     */
    public function delete($url,$query = ''){
        curl_setopt(self::$ch,CURLOPT_CUSTOMREQUEST,"DELETE");
        curl_setopt(self::$ch, CURLOPT_URL, $url);
        curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
        return self::request($url);
    }
    /**
     *  设置请求头
     */
    public function setHeader($param = array())
    {
        curl_setopt(self::$ch, CURLOPT_HTTPHEADER, $param);
        return self::$instance;
    }

    /**
     *  获取报头
     **/
    public function getHeader()
    {
        return self::$header;
    }

    /**
     *  获取HTTP状态码
     **/
    public function getHttpCode()
    {
        if (is_resource(self::$ch)) {
            self::$httpCode = curl_getinfo(self::$ch, CURLINFO_HTTP_CODE);
        }
        return self::$httpCode;
    }

    /**
     *  获取CURL信息
     **/
    public function getCurlInfo()
    {
        if (is_resource(self::$ch)) {
            self::$curlInfo = curl_getinfo(self::$ch,CURLINFO_HEADER_OUT);
        }
        return self::$curlInfo;
    }

    /**
     *  转码
     **/
    private function toUtf8($str)
    {
        if (json_encode($str) == 'null') {
            return iconv('GB2312', 'UTF-8//IGNORE', $str);
        }
        return $str;
    }

    /**
     *  打印错误
     **/
    private function sendError($errMsg)
    {
        echo "<br/>ERROR_INFO:{$errMsg}<br/>";
    }

    /**
     *  关闭句柄
     **/
    private function close()
    {
        if (is_resource(self::$ch)) {
            curl_close(self::$ch);
        }
    }

    /**
     *  析构函数
     **/
    public function __destruct()
    {
        self::close();
    }

}

/* test demo
$url = "url";
$curl = Curl::getInstance(['CURLINFO_HEADER_OUT'=>1]);
$result = $curl->setHeader(['Content-Type:application/json'])->get($url, array("email" => "feng@gmail.com", "password" => "secret"));
$curlInfo = $curl->getCurlInfo();
$header = $curl->getHeader();
$httpCode = $curl->getHttpCode();
*/
?>