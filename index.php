<?php

session_start();

// debug

//ini_set("display_errors", 1);
//ini_set("error_reporting", E_ALL);

require 'vendor/autoload.php';

use QQLogin\QQLogin;
use QQLogin\Error\QQError;

// appKey 为避免泄露，已存入 Redis

$redis = new \Redis();
$redis->connect('redis');
$appKey = $redis->get('qq_login_appkey');

const APP_ID = 101407196;
const CALLBACK = 'https://login.khs1994.com/';
const SCOPE = 'get_user_info';

try {
    $qq = new QQLogin(APP_ID, $appKey, CALLBACK);
} catch (QQError $e) {
    $e->showError();
}

if (isset($_GET['code']) && isset($_GET['state'])) {
    $access_token = $qq->getAccessToken();
    $openid = $qq->getOpenId();

    // 将 Access_token OpenId 存入 session

    $_SESSION['access_token'] = $access_token;
    $_SESSION['openid'] = $openid;
    $_SESSION['status'] = true;

    header('Location:.');
}

// 如果地址带有 logout=true 说明用户点击了退出，清空相关信息

if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    $_SESSION['status'] = false;
}

// 如果 session 带有 status 为 true 则，说明从 callback 接口跳转过来

if (isset($_SESSION['status']) && $_SESSION['status'] === true) {
    $openid = $_SESSION['openid'];
    $access_token = $_SESSION['access_token'];

    // session 是否存在相关信息
    if (empty($_SESSION['QQ_USER_DATA'])) {

        // 获取基本信息

        $array = $qq->api()->get_user_info();
    } else {
        $array = $_SESSION['QQ_USER_DATA'];
    }

    // 返回 json

    header('Content-type: application/json;charset=utf8');
    echo json_encode($array);
} else {
    if (isset($_GET['login']) && $_GET['login'] === 'true') {
        $qq->getLoginUrl();
        //登录成功之后跳转到 响应页面
    } else {
        echo <<<'EOF'
    <a href="?login=true"><img src="https://qzonestyle.gtimg.cn/qzone/vas/opensns/res/img/bt_92X120.png"></a>
EOF;
    }
}
