<?php

namespace QQLogin;

class QQError extends \Error
{
    private $errorArray;
    protected $code;
    protected $message;

    public function __construct(int $code, string $message = null)
    {
        $this->errorArray = ['20001' => '配置项错误。', '30001' => 'The state does not match. You may be a victim of CSRF.',
            '50001' => '可能是服务器无法请求 https 协议,可能未开启 curl 扩展,请尝试开启 curl 扩展，重启 web 服务器。',];
        $this->code = $code;
        $this->message = $message;
    }

    public function showError()
    {
        $code = $this->code;
        $message = $this->message;
        if (!$message) {
            if (key_exists($code, $this->errorArray)) {
                $message = $this->errorArray[$code];
            } else {
                $message = 'Error';
            }
        }

        header('Content-Type: application/json');
        die(json_encode(['ret' => $code, 'msg' => $message,]));
    }
}
