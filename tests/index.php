<?php

session_start();

//define('BASEDIR', __DIR__.'/../src');
//
//spl_autoload_register(function ($className) {
//    require BASEDIR.'/'.str_replace('\\', '/', $className).'.php';
//});

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

// 如果地址带有 logout=true 说明用户点击了退出，清空相关信息

if ($_GET['logout'] === 'true') {
    $_SESSION['status'] = false;
}

// 如果 session 带有 status 为 true 则，说明从 callback 接口跳转过来

if ($_SESSION['status'] === true) {
    $openid = $_SESSION['openid'];
    $access_token = $_SESSION['access_token'];

    // session 是否存在相关信息
    if (empty($_SESSION['QQ_USER_DATA'])) {

        // 获取基本信息

        $qq = new QQLogin($config);
        $array = $qq->call->get_user_info();
    } else {
        $array = $_SESSION['QQ_USER_DATA'];
    }

    // 返回 json

    header('Content-type: application/json;charset=utf8');
    echo json_encode($array);

} else {
    if ($_GET['login'] === 'true') {
        $qq = new QQLogin($config);
        $qq->getLoginUrl();
        //登录成功之后跳转到 响应页面
    } else {
        echo <<<'EOF'
    <a href="?login=true"><img src="https://qzonestyle.gtimg.cn/qzone/vas/opensns/res/img/bt_92X120.png"></a>
EOF;
    }
}
