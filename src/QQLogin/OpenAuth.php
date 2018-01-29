<?php

namespace QQLogin;

class OpenAuth
{
    const VERSION = 'v18.01';
    const GET_AUTH_CODE_URL = 'https://graph.qq.com/oauth2.0/authorize';
    const GET_ACCESS_TOKEN_URL = 'https://graph.qq.com/oauth2.0/token';
    const GET_OPENID_URL = 'https://graph.qq.com/oauth2.0/me';

    use Config;

    // 第一步，拼接请求 QQ 登录页

    public function getLoginUrl()
    {
        // 读取原始配置

        $appid = $this->readConfig('appid');
        $callback = $this->readConfig('callback');
        $scope = $this->readConfig('scope');

        // 生成唯一随机串防 CSRF 攻击

        $state = md5(uniqid(rand(), true));

        // 写入动态配置

        var_dump($_SESSION);

        $this->set('state', $state);

        // 构造请求参数列表

        $array = [
            'response_type' => 'code',
            'client_id' => $appid,
            'redirect_uri' => $callback,
            'state' => $state,
            'scope' => $scope,
        ];

        $login_url = self::GET_AUTH_CODE_URL . '?' . http_build_query($array);

        // 第二步，跳转网址，用户输入 QQ 账号密码

        header("Location:$login_url");
    }

    // 第三步，通过 GET 方法获得的 code 来获取 Access_token

    public function getAccessToken()
    {

        $state = $this->get('state');

        // 验证 state 防止 CSRF 攻击

        if ($_GET['state'] !== $state) {
            $this->error->showError('30001');
        }

        $code = $_GET['code'];

        // 请求参数列表

        $array = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->readConfig('appid'),
            'redirect_uri' => $this->readConfig('callback'),
            'client_secret' => $this->readConfig('appkey'),
            'code' => $code,
        ];

        // 构造请求 access_token 的 url

        $token_url = self::GET_ACCESS_TOKEN_URL . '?' . http_build_query($array);
        $response = $this->curl->get($token_url);
        if (strpos($response, 'callback') !== false) {
            $lpos = strpos($response, '(');
            $rpos = strrpos($response, ')');
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
            $msg = json_decode($response);

            if (isset($msg->error)) {
                $this->error->showError($msg->error, $msg->error_description);
            }
        }

        $params = [];
        parse_str($response, $params);

        $access_token = $params['access_token'];

        $this->set('access_token', $access_token);

        // 返回 Access_token

        return $access_token;
    }

    // 获取用户 OpenId

    public function getOpenId()
    {
        // 请求参数列表

        $array = [
            'access_token' => $this->get('access_token'),
        ];

        $graph_url = self::GET_OPENID_URL . '?' . http_build_query($array);
        $response = $this->curl->get($graph_url);

        // 检测错误是否发生

        if (strpos($response, 'callback') !== false) {
            $lpos = strpos($response, '(');
            $rpos = strrpos($response, ')');
            $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
        }

        $user = json_decode($response);
        if (isset($user->error)) {
            $this->error->showError($user->error, $user->error_description);
        }

        // 记录 openid

        $openId = $user->openid;

        $this->set('openid', $openId);

        return $openId;
    }
}