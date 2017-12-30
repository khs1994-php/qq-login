<?php
session_start();

define("BASEDIR", __DIR__ . "/../src");

spl_autoload_register(function ($className) {
    require BASEDIR . "/" . str_replace("\\", "/", $className) . ".php";
});

// require 'vendor/autoload.php';

use QQLogin\Oauth;

$_SESSION['status']=true;

// 请求 access_token

$oauth = new Oauth();
$access_token = $oauth->callback();
$openid = $oauth->getOpenId();

$_SESSION['access_token']=$access_token;
$_SESSION['openid']=$openid;

echo "<meta http-equiv='refresh' content='0.01;url=index.php'>";
//响应之后跳转到展示页面
