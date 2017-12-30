<?php

/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

namespace QQLogin;

class Recorder
{
    private static $data;
    private $inc;
    private $error;

    public function __construct(array $config = [])
    {
        $this->error = new ErrorCase();

        // 读取配置文件
        $config = json_decode(file_get_contents('config.json'));
        $this->inc = $config;
        if (empty($this->inc)) {
            $this->error->showError('20001');
        }

        if (empty($_SESSION['QC_userData'])) {
            self::$data = [];
        } else {
            self::$data = $_SESSION['QC_userData'];
        }
    }

    public function write($name, $value)
    {
        self::$data[$name] = $value;
    }

    public function read($name)
    {
        if (empty(self::$data[$name])) {
            return;
        } else {
            return self::$data[$name];
        }
    }

    public function readInc($name)
    {
        if (empty($this->inc->$name)) {
            return;
        } else {
            return $this->inc->$name;
        }
    }

    public function delete($name)
    {
        unset(self::$data[$name]);
    }

    public function __destruct()
    {
        $_SESSION['QC_userData'] = self::$data;
    }
}
