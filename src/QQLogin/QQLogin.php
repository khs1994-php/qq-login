<?php

namespace QQLogin;

use QQLogin\Error\QQError;

/**
 * Class QQLogin
 *
 * @method OpenAuth getAccessToken()
 * @method OPenAuth getOpenId()
 * @method OPenAuth getLoginUrl()
 * @method Api api()
 * @package QQLogin
 */
class QQLogin
{
    /**
     *
     * 构造函数写入配置
     *
     * @param int    $appId
     * @param string $appKey
     * @param string $callback
     * @param string $scope
     * @param bool   $errorReport
     * @param string $drive
     * @throws QQError
     */
    public function __construct(int $appId,
                                string $appKey,
                                string $callback,
                                string $scope = 'get_user_info',
                                bool $errorReport = true,
                                string $drive = 'session')
    {
        $_SESSION['QQ_CONFIG_DATA'] = [
            'appid' => $appId,
            'appkey' => $appKey,
            'callback' => $callback,
            'scope' => $scope,
            'drive' => $drive,
        ];
    }

    public function __call($name, $value)
    {
        switch ($name) {
            case "api":

                return new Api();

            default:
                $openAuth = new OpenAuth();

                return $openAuth->$name();
        }
    }
}
