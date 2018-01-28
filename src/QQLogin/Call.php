<?php

/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

namespace QQLogin;

/*
 * @brief QC类，api外部对象，调用接口全部依赖于此对象
 * */
class Call extends Oauth
{
    private $array;
    private $APIMap;

    /**
     * _construct.
     *
     * 构造方法
     *
     *
     * @param string $access_token access_token value
     * @param string $openid openid value
     * @param array $config
     *
     */
    public function __construct(string $access_token = null, string $openid = null, array $config = [])
    {
        parent::__construct($config);

        //如果access_token和openid为空，则从session里去取，适用于demo展示情形
        if ($access_token === null || $openid === null) {
            $this->array = [
                'oauth_consumer_key' => (int)$this->config->readConfig('appid'),
                'access_token' => $this->config->get('access_token'),
                'openid' => $this->config->get('openid'),
            ];
        } else {
            $this->array = [
                'oauth_consumer_key' => (int)$this->config->readConfig('appid'),
                'access_token' => $access_token,
                'openid' => $openid,
            ];
        }

        //初始化APIMap
        /*
         * 加#表示非必须，无则不传入url(url中不会出现该参数)， "key" => "val" 表示key如果没有定义则使用默认值val
         * 规则 array( baseUrl, argListArr, method)
         */
        $this->APIMap = [

            /*                       qzone                    */
            'add_blog' => [
                'https://graph.qq.com/blog/add_one_blog',
                ['title', 'format' => 'json', 'content' => null],
                'POST',
            ],
            'add_topic' => [
                'https://graph.qq.com/shuoshuo/add_topic',
                ['richtype', 'richval', 'con', '#lbs_nm', '#lbs_x', '#lbs_y', 'format' => 'json', '#third_source'],
                'POST',
            ],
            'get_user_info' => [
                'https://graph.qq.com/user/get_user_info',
                ['format' => 'json'],
                'GET',
            ],
            'add_album' => [
                'https://graph.qq.com/photo/add_album',
                ['albumname', '#albumdesc', '#priv', 'format' => 'json'],
                'POST',
            ],
            'upload_pic' => [
                'https://graph.qq.com/photo/upload_pic',
                ['picture', '#photodesc', '#title', '#albumid', '#mobile', '#x', '#y', '#needfeed', '#successnum', '#picnum', 'format' => 'json'],
                'POST',
            ],
            'list_album' => [
                'https://graph.qq.com/photo/list_album',
                ['format' => 'json'],
            ],
            'check_page_fans' => [
                'https://graph.qq.com/user/check_page_fans',
                ['page_id' => '314416946', 'format' => 'json'],
            ],
            /*                           pay                          */

            'get_tenpay_addr' => [
                'https://graph.qq.com/cft_info/get_tenpay_addr',
                ['ver' => 1, 'limit' => 5, 'offset' => 0, 'format' => 'json'],
            ],
        ];
    }

    //调用相应api

    private function applyAPI($arr, $argsList, string $baseUrl, string $method)
    {
        $pre = '#';
        $keysArr = $this->array;

        $optionArgList = []; //一些多项选填参数必选一的情形
        foreach ($argsList as $key => $val) {
            $tmpKey = $key;
            $tmpVal = $val;

            if (!is_string($key)) {
                $tmpKey = $val;

                if (strpos($val, $pre) === 0) {
                    $tmpVal = $pre;
                    $tmpKey = substr($tmpKey, 1);
                    if (preg_match("/-(\d$)/", $tmpKey, $res)) {
                        $tmpKey = str_replace($res[0], '', $tmpKey);
                        $optionArgList[$res[1]][] = $tmpKey;
                    }
                } else {
                    $tmpVal = null;
                }
            }

            // 如果没有设置相应的参数
            if (!isset($arr[$tmpKey]) || $arr[$tmpKey] === '') {
                if ($tmpVal === $pre) {//则使用默认的值
                    continue;
                } elseif ($tmpVal) {
                    $arr[$tmpKey] = $tmpVal;
                } else {
                    if ($v = $_FILES[$tmpKey]) {
                        $filename = dirname($v['tmp_name']) . '/' . $v['name'];
                        move_uploaded_file($v['tmp_name'], $filename);
                        $arr[$tmpKey] = "@$filename";
                    } else {
                        $this->error->showError('api调用参数错误', "未传入参数$tmpKey");
                    }
                }
            }

            $keysArr[$tmpKey] = $arr[$tmpKey];
        }
        // 检查选填参数必填一的情形
        foreach ($optionArgList as $val) {
            $n = 0;
            foreach ($val as $v) {
                if (in_array($v, array_keys($keysArr))) {
                    $n++;
                }
            }

            if (!$n) {
                $str = implode(',', $val);
                $this->error->showError('api调用参数错误', $str . '必填一个');
            }
        }

        if ($method === 'POST') {
            if ($baseUrl === 'https://graph.qq.com/blog/add_one_blog') {
                $response = $this->curl->post($baseUrl, $keysArr);
            } else {
                $response = $this->curl->post($baseUrl, $keysArr);
            }
        } elseif ($method === 'GET') {
            $response = $this->curl->get($baseUrl . '?' . http_build_query($keysArr));
        }

        return $response;
    }

    /**
     * _call
     * 魔术方法，做api调用转发.
     *
     * @param string $name 调用的方法名称
     * @param array $arg 参数列表数组
     *
     * @return array          返加调用结果数组
     */
    public function __call(string $name, array $arg)
    {
        // 如果APIMap不存在相应的api
        if (empty($this->APIMap[$name])) {
            $this->error->showError('api调用名称错误', "不存在的API: <span style='color:red;'>$name</span>");
        }


        // 从APIMap获取api相应参数
        $baseUrl = $this->APIMap[$name][0];
        $argsList = $this->APIMap[$name][1];
        $method = isset($this->APIMap[$name][2]) ? $this->APIMap[$name][2] : 'GET';

        if (empty($arg)) {
            $arg[0] = null;
        }
        // 对于get_tenpay_addr，特殊处理，php json_decode对\xA312此类字符支持不好
        if ($name !== 'get_tenpay_addr') {
            $response = json_decode($this->applyAPI($arg[0], $argsList, $baseUrl, $method));
            $responseArr = $this->objToArr($response);
        } else {
            $responseArr = $this->simple_json_parser($this->applyAPI($arg[0], $argsList, $baseUrl, $method));
        }
        // 检查返回ret判断api是否成功调用
        if ($responseArr['ret'] === 0) {
            return $responseArr;
        } else {
            $this->error->showError($response->ret, $response->msg);
        }
    }

    // php 对象到数组转换
    private function objToArr($obj)
    {
        if (!is_object($obj) && !is_array($obj)) {
            return $obj;
        }
        $arr = [];
        foreach ($obj as $k => $v) {
            $arr[$k] = $this->objToArr($v);
        }

        return $arr;
    }

    /**
     * get_access_token
     * 获得access_token.
     *
     * @param void
     *
     * @return string 返加access_token
     */
    public function getAccessToken()
    {
        return $this->config->get('access_token');
    }

    // 简单实现json到php数组转换功能
    private function simple_json_parser(string $json)
    {
        $json = str_replace('{', '', str_replace('}', '', $json));
        $jsonValue = explode(',', $json);
        $arr = [];
        foreach ($jsonValue as $v) {
            $jValue = explode(':', $v);
            $arr[str_replace('"', '', $jValue[0])] = (str_replace('"', '', $jValue[1]));
        }

        return $arr;
    }
}
