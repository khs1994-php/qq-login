<?php

session_start();

define('BASEDIR', __DIR__.'/../src');

spl_autoload_register(function ($className) {
    require BASEDIR.'/'.str_replace('\\', '/', $className).'.php';
});

// require 'vendor/autoload.php';

use QQLogin\QQCall;
use QQLogin\Oauth;

$config=[
    "appid"=>"101440339",
    "appkey"=> "ac1c9a426b3685d61c928c5ee3509c7a",
    "callback"=> "http://demo.khs1994.com/tests/callback.php",
    "scope"=>"get_user_info",
    "errorReport"=> true,
    ];

if ($_GET['logout'] === 'true') {
    $_SESSION['status'] = false;
}

if ($_SESSION['status'] === true) {
    $openid = $_SESSION['openid'];
    $access_token = $_SESSION['access_token'];
    if (!$_SESSION['nickname']) {
        $qc = new QQCall($access_token, $openid,$config);
        $res = $qc->get_user_info();
        $nickname = $res['nickname'];
        $head_url = $res['figureurl_qq_2'];
        $_SESSION['nickname'] = $nickname;
        $_SESSION['head_url'] = $head_url;
    } else {
        $nickname = $_SESSION['nickname'];
        $head_url = $_SESSION['head_url'];
    }
    echo <<<EOF
<a href="?logout=true">退出登录</a>
<hr>
$nickname
<br>
$head_url

EOF;
} else {
    if ($_GET['login'] === 'true') {
        $oauth = new Oauth($config);
        $oauth->login();
    //登录成功之后跳转到 响应页面
    } else {
        echo <<<'EOF'
    <a href="?login=true"><img src="https://qzonestyle.gtimg.cn/qzone/vas/opensns/res/img/bt_92X120.png"></a>
EOF;
    }
}
