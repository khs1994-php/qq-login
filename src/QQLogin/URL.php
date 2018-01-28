<?php

/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

namespace QQLogin;

/*
 * @brief url封装类，将常用的url请求操作封装在一起
 * */

class URL
{
    private $error;

    public function __construct()
    {
        $this->error = new ErrorCase($config);
    }

    /**
     * get_contents
     * 服务器通过get请求获得内容.
     *
     * @param string $url 请求的url,拼接后的
     *
     * @return string           请求返回的内容
     */
    public function getContents($url)
    {
        if (ini_get('allow_url_fopen') === '1') {
            $response = file_get_contents($url);
        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response = curl_exec($ch);
            curl_close($ch);
        }

        //-------请求为空
        if (empty($response)) {
            $this->error->showError('50001');
        }

        return $response;
    }

    /**
     * get
     * get方式请求资源.
     *
     * @param string $url 基于的baseUrl
     * @param array $keysArr 参数列表数组
     *
     * @return string         返回的资源内容
     */
    public function get($url, $keysArr)
    {
        $url = $url.http_build_query($keysArr);

        return $this->getContents($combined);
    }

    /**
     * post
     * post方式请求资源.
     *
     * @param string $url 基于的baseUrl
     * @param array $keysArr 请求的参数列表
     * @param int $flag 标志位
     *
     * @return string           返回的资源内容
     */
    public function post($url, $keysArr, $flag = 0)
    {
        $ch = curl_init();
        if (!$flag) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $keysArr);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);

        return $ret;
    }
}
