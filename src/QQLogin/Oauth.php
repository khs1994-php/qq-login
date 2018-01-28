<?php

namespace QQLogin;

use Curl\Curl;

class Oauth
{
    const VERSION = '2.0';
    const GET_AUTH_CODE_URL = 'https://graph.qq.com/oauth2.0/authorize';
    const GET_ACCESS_TOKEN_URL = 'https://graph.qq.com/oauth2.0/token';
    const GET_OPENID_URL = 'https://graph.qq.com/oauth2.0/me';

    protected $config;
    public $curl;
    protected $error;

    public function __construct(array $config=[])
    {
        $this->config = new Config($config);
        $this->error = new Error();
        $this->curl=new Curl();
    }

    public function login()
    {
        // 读取配置

        $appid = $this->config->readConfig('appid');
        $callback = $this->config->readConfig('callback');
        $scope = $this->config->readConfig('scope');

        // 生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), true));

        // 写入配置

        $this->config->set('state', $state);

        // 构造请求参数列表

        $keysArr = [
            'response_type' => 'code',
            'client_id' => $appid,
            'redirect_uri' => $callback,
            'state' => $state,
            'scope' => $scope,
        ];

        $login_url = self::GET_AUTH_CODE_URL.'?'.http_build_query($keysArr);

        // 跳转网址

        header("Location:$login_url");
    }

    public function callback()
    {
        $state = $this->config->get('state');

        // 验证 state 防止 CSRF 攻击
        if ($_GET['state'] !== $state) {
            $this->error->showError('30001');
        }

        // 请求参数列表
        $keysArr = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->readConfig('appid'),
            'redirect_uri' => $this->config->readConfig('callback'),
            'client_secret' => $this->config->readConfig('appkey'),
            'code' => $_GET['code'],
        ];

        // 构造请求access_token的url
        $token_url=self::GET_ACCESS_TOKEN_URL.'?'.http_build_query($keysArr);
        $response = urldecode($this->curl->get($token_url));

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

        $this->config->set('access_token', $params['access_token']);

        return $params['access_token'];
    }

    public function getOpenId()
    {

        // 请求参数列表
        $keysArr = [
            'access_token' => $this->config->get('access_token'),
        ];

        $graph_url = self::GET_OPENID_URL.'?'.http_build_query($keysArr);
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

        // 记录openid
        $this->config->set('openid', $user->openid);

        return $user->openid;
    }
}
