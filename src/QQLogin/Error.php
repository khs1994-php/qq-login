<?php

namespace QQLogin;

/**
 *
 * @brief ErrorCase类，封闭异常
 *
 */

class Error
{
    private $errorMsg;
    private $recorder;

    public function __construct()
    {
        $this->errorMsg = [
            '20001' => '配置项错误。',
            '30001' => 'The state does not match. You may be a victim of CSRF.',
            '50001' => '可能是服务器无法请求 https 协议,可能未开启 curl 扩展,请尝试开启 curl 扩展，重启 web 服务器。',
        ];
    }

    /**
     * showError
     * 显示错误信息.
     *
     * @param string $code 错误代码
     * @param string $description 描述信息（可选）
     *
     * @return array
     */
    public function showError(string $code, string $description = null)
    {
        if ($description === null) {
            die($this->errorMsg[$code]);
        } else {
            header("Content-Type: application/json");
            echo json_encode([
                'ret' => $code,
                'msg' => $description,
            ]);
        }
    }
}
