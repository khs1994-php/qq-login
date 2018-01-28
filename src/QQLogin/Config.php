<?php

namespace QQLogin;

class Config
{
    private static $data;
    private $config;
    private $error;

    public function __construct(array $config)
    {
        $this->error = new Error();

        $this->config = (object)$config;
        if (empty($this->config)) {
            $this->error->showError('20001');
        }

        if (empty($_SESSION['QC_userData'])) {
            self::$data = [];
        } else {
            self::$data = $_SESSION['QC_userData'];
        }
    }

    public function set($name, $value)
    {
        self::$data[$name] = $value;
    }

    public function get($name)
    {
        if (empty(self::$data[$name])) {
            return 0;
        } else {
            return self::$data[$name];
        }
    }

    public function readConfig($name)
    {
        if (empty($this->config->$name)) {
            return 0;
        } else {
            return $this->config->$name;
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
