<?php
/**
 * Created by PhpStorm.
 * User: khs1994
 * Date: 29/01/2018
 * Time: 2:11 PM
 */

namespace QQLogin;

class QQLogin
{
    private $openAuth;

    public $call;

    // 构造函数写入配置

    public function __construct(array $config)
    {
        $_SESSION['QQ_SOURCE_DATA'] = $config;

        $this->call = new Call();
        $this->openAuth = new OpenAuth();

        // 如果配置为空，返回错误

        if (empty($config)) {
            $error = new Error();
            $error->showError('20001');
        }
    }

    public function __call($name, $value)
    {
        return $this->openAuth->$name();
    }
}