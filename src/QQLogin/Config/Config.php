<?php

namespace QQLogin\Config;

use Curl\Curl;

trait Config
{
    public static $data;

    public $config;

    public $curl;

    public function __construct()
    {
        $this->curl = new Curl();

        $this->config = (object) $_SESSION['QQ_CONFIG_DATA'];

        self::$data = empty($_SESSION['QQ_DATA']) ? [] : $_SESSION['QQ_DATA'];
    }

    /**
     * 设置单个配置
     *
     * @param string $name
     * @param string $value
     */
    public function set(string $name, string $value)
    {
        self::$data[$name] = $value;
        $_SESSION['QQ_DATA'] = self::$data;
    }

    /**
     * 获取单个配置
     *
     * @param string $name
     * @return int
     */
    public function get(string $name)
    {
        if (empty(self::$data[$name])) {
            return 0;
        } else {

            return self::$data[$name];
        }
    }

    /**
     * 删除单个配置
     *
     * @param string $name
     */
    public function delete(string $name)
    {
        unset(self::$data[$name]);
        $_SESSION['QQ_DATA'] = self::$data;
    }

    /**
     * 读取原始配置
     *
     * @param string $name
     * @return int
     */
    public function readConfig(string $name)
    {
        if (empty($this->config->$name)) {
            return 0;
        } else {

            return $this->config->$name;
        }
    }
}
