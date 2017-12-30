# QQ 登录 SDK

[![GitHub stars](https://img.shields.io/github/stars/khs1994-php/qq-login.svg?style=social&label=Stars)](https://github.com/khs1994-php/qq-login) [![StyleCI](https://styleci.io/repos/101897554/shield?branch=master)](https://styleci.io/repos/101897554) [![PHP from Packagist](https://img.shields.io/packagist/php-v/khs1994/qq-login.svg)](https://packagist.org/packages/khs1994/qq-login) [![GitHub (pre-)release](https://img.shields.io/github/release/khs1994-php/qq-login/all.svg)](https://github.com/khs1994-php/qq-login/releases)

`composer` 引入，编辑 `composer.json`

```json
{
  "require":{
    "khs1994/qq-login":"dev-master"
  }
}
```

执行以下命令引入

```bash
$ composer install
```

# 使用方法

## 编辑配置文件 `config.json`

```json
{
  "appid": "101440339",
  "appkey": "ac1c9a426b3685d61c928c5ee3509c7a",
  "callback": "http://demo.khs1994.com/tests/callback.php",
  "scope": "get_user_info",
  "errorReport": true,
  "storageType": "file"
}
```

## 请求登录页面 `index.php`

```php
<?php

require 'vendor/autoload.php';

use QQLogin\Oauth;

$oauth = new Oauth();
$oauth->login();
```

## 响应页面 `callback.php`

```php
<?php

require 'vendor/autoload.php';

use QQLogin\Oauth;

$oauth = new Oauth();
$access_token = $oauth->callback();
$openid = $oauth->getOpenId();


# 跳转到登录之后的目标页
echo "<meta http-equiv='refresh' content='0.01;url=index.php'>";
```

## 目标页面 `index.php`

> 目标页面仍然为 `index.php`,其通过 `session` 来判断。

```php
<?php

use QQLogin\QC;

$qc = new QC($access_token, $openid);
$res = $qc->get_user_info();

// 数组 $res 数组包含用户信息，解析该数组即可。
```

## Demo

以上请求登录页面，响应页面，目标页面示例位于 `tests` 文件夹内。

修改配置文件 `config.json` 之后，访问 `/tests/index.php` 登录，即可返回昵称与头像链接。
