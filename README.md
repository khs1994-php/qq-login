# QQ 登录 SDK

[![GitHub stars](https://img.shields.io/github/stars/khs1994-php/qq-login.svg?style=social&label=Stars)](https://github.com/khs1994-php/qq-login) [![StyleCI](https://styleci.io/repos/101897554/shield?branch=master)](https://styleci.io/repos/101897554) [![PHP from Packagist](https://img.shields.io/packagist/php-v/khs1994/qq-login.svg)](https://packagist.org/packages/khs1994/qq-login) [![GitHub (pre-)release](https://img.shields.io/github/release/khs1994-php/qq-login/all.svg)](https://github.com/khs1994-php/qq-login/releases)

# 安装

```bash
$ composer require khs1994/qq-login @dev
```

# 使用方法

```php
<?php

require 'vendor/autoload.php';

use QQLogin\QQLogin;

$config = [
    'appid' => '8888...',
    'appkey' => 'XXXX...',
    'callback' => 'http://demo.khs1994.com/tests/callback.php',
    'scope' => 'get_user_info',
    'errorReport' => true,
];

$qq = new QQLogin($config);

# 请求 QQ 登录页

$qq->getLoginUrl();

# 回调地址中再次请求密钥

$access_token = $qq->getAccessToken();

$openid = $qq->getOpenId();

# 通过密钥等获取用户信息，返回数组

$array = $qq->call->get_user_info();
```

## 示例

请查看 [`index.php`](index.php)

# 感谢

* [QQ互联](https://connect.qq.com/index.html)

* [khs1994/curl](https://github.com/khs1994-php/curl)

* [khs1994.com](https://developer.khs1994.com)