<?php

session_start();

require '../vendor/autoload.php';

use QQLogin\QQLogin;

// 必要的配置项

$config = [
    'appid' => '101440339',
    'appkey' => 'ac1c9a426b3685d61c928c5ee3509c7a',
    'callback' => 'http://demo.khs1994.com/tests/callback.php',
    'scope' => 'get_user_info',
    'errorReport' => true,
];

$_SESSION['status'] = true;

// 请求 access_token

$qq = new QQLogin($config);

$access_token = $qq->getAccessToken();

// 通过 Access_token 请求 OpenId

$openid = $qq->getOpenId();

// 将 Access_token OpenId 存入 session

$_SESSION['access_token'] = $access_token;
$_SESSION['openid'] = $openid;

// 响应之后跳转到展示页面

echo "<meta http-equiv='refresh' content='0.01;url=index.php'>";