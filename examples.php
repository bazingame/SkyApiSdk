<?php
/**
 * Author: Howard
 * CreateTime: 19-2-26 下午4:56
 * Description:
 */

include_once "config.php";
include_once "SkyApi.php";


$skyapi = new SkyApi(DEVEMMAIL, DEVPASSWORD);

//example 1 -- get developer infomation by function which SkyApi class provide
$devInfo = $skyapi->getDevSelfInfo();

//example 2 -- get developer infomation by you self
$infoUrl = APIBASE . '/perm/user/' . DEVEMMAIL;
$devInfo = $skyapi->curl->get($infoUrl);


//example 3 -- get student course data by function which SkyApi class provide
$skyapi->loginPassport('sid', 2016500001, 'secret');
$res = $skyapi->getCourse();

//example 4 -- get student course data by you self
$loginUrl = APIBASE . '/passport/login/';
$skyapi->curl->post($loginUrl, ['type' => 'sid', 'sid' => 2016500001, 'password' => 'secret']);
$gradeUrl = APIBASE . '/edu/course/';
$grade = $skyapi->curl->get($gradeUrl);
