<?php
/**
 * Created by PhpStorm.
 * User: khs1994
 * Date: 29/01/2018
 * Time: 2:30 PM.
 */

namespace QQLogin;

use Curl\Curl;

trait Config
{
    public static $data;

    public $config;

    public $curl;

    public $error;

    public function __construct()
    {
        $this->config = (object) $_SESSION['QQ_SOURCE_DATA'];

        if (empty($_SESSION['QQ_DATA'])) {
            self::$data = [];
        } else {
            self::$data = $_SESSION['QQ_DATA'];
        }

        $this->curl = new Curl();
        $this->error = new Error();
    }

    public function set(string $name, string $value)
    {
        self::$data[$name] = $value;
        $_SESSION['QQ_DATA'] = self::$data;
    }

    // 获取单个配置

    public function get(string $name)
    {
        if (empty(self::$data[$name])) {
            return 0;
        } else {
            return self::$data[$name];
        }
    }

    // 删除单个配置

    public function delete(string $name)
    {
        unset(self::$data[$name]);
        $_SESSION['QQ_DATA'] = self::$data;
    }

    // 读取原始配置

    public function readConfig(string $name)
    {
        if (empty($this->config->$name)) {
            return 0;
        } else {
            return $this->config->$name;
        }
    }
}
