<?php

session_start();

require '../vendor/autoload.php';

use QQLogin\Oauth;

$config = [
    'appid' => '101440339',
    'appkey' => 'ac1c9a426b3685d61c928c5ee3509c7a',
    'callback' => 'http://demo.khs1994.com/tests/callback.php',
    'scope' => 'get_user_info',
    'errorReport' => true,
];

$_SESSION['status'] = true;

// 请求 access_token

$oauth = new Oauth($config);
$access_token = $oauth->getAccessToken();
$openid = $oauth->getOpenId();

$_SESSION['access_token'] = $access_token;
$_SESSION['openid'] = $openid;

echo "<meta http-equiv='refresh' content='0.01;url=index.php'>";
//响应之后跳转到展示页面
