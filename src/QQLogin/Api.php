<?php

namespace QQLogin;

use QQLogin\Error\QQError;
use QQLogin\Config\Config;

/**
 * 通过 OpenId Access_token 等来调用 QQ Api.
 *
 * 例如获取用户基本信息
 *
 * @method add_blog()
 * @method add_topic()
 * @method get_user_info()
 * @method add_album()
 * @method upload_pic()
 * @method list_album()
 * @method check_page_fans()
 * @method get_tenpay_addr()
 */
class Api
{
    /**
     * 加#表示非必须，无则不传入url(url中不会出现该参数)， "key" => "val" 表示key如果没有定义则使用默认值val
     * 规则 array( baseUrl, argListArr, method)
     *
     */
    const API_MAP = [
        'add_blog' => [
            'https://graph.qq.com/blog/add_one_blog',
            ['title',
                'format' => 'json',
                'content' => null
            ],
            'POST',
        ],
        'add_topic' => [
            'https://graph.qq.com/shuoshuo/add_topic',
            ['richtype',
                'richval',
                'con',
                '#lbs_nm',
                '#lbs_x',
                '#lbs_y',
                'format' => 'json',
                '#third_source'
            ],
            'POST',
        ],
        'get_user_info' => [
            'https://graph.qq.com/user/get_user_info',
            ['format' => 'json'],
        ],
        'add_album' => [
            'https://graph.qq.com/photo/add_album',
            ['albumname',
                '#albumdesc',
                '#priv',
                'format' => 'json'
            ],
            'POST',
        ],
        'upload_pic' => [
            'https://graph.qq.com/photo/upload_pic',
            ['picture',
                '#photodesc',
                '#title',
                '#albumid',
                '#mobile',
                '#x',
                '#y',
                '#needfeed',
                '#successnum',
                '#picnum',
                'format' => 'json',
            ],
            'POST',
        ],
        'list_album' => [
            'https://graph.qq.com/photo/list_album',
            ['format' => 'json']
        ],
        'check_page_fans' => [
            'https://graph.qq.com/user/check_page_fans',
            ['page_id' => '314416946',
                'format' => 'json'
            ],
        ],
        'get_tenpay_addr' => [
            'https://graph.qq.com/cft_info/get_tenpay_addr',
            ['ver' => 1,
                'limit' => 5,
                'offset' => 0,
                'format' => 'json'],
        ],
    ];

    private $array;

    use Config;

    /**
     * 调用相应 api
     *
     * @param        $arr
     * @param        $argsList
     * @param string $baseUrl
     * @param string $method
     * @return mixed
     * @throws QQError
     */
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
                if ($tmpVal === $pre) {
                    //则使用默认的值
                    continue;
                } elseif ($tmpVal) {
                    $arr[$tmpKey] = $tmpVal;
                } else {
                    if ($v = $_FILES[$tmpKey]) {
                        $filename = dirname($v['tmp_name']).'/'.$v['name'];
                        move_uploaded_file($v['tmp_name'], $filename);
                        $arr[$tmpKey] = "@$filename";
                    } else {
                        throw new QQError(2, "API 调用参数错误，未传入参数 $tmpKey");
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
                throw new QQError(3, "api调用参数错误 $str 必填一个");
            }
        }

        if ($method === 'POST') {
            $response = $this->curl->post($baseUrl, $keysArr);
        } else {
            $response = $this->curl->get($baseUrl.'?'.http_build_query($keysArr));
        }

        return $response;
    }

    /**
     * 魔术方法，做 api 调用转发.
     *
     * @param string $name 调用的方法名称
     * @param array  $arg  参数列表数组
     *
     * @return array          返加调用结果数组
     * @throws QQError
     */
    public function __call($name, $arg)
    {
        // 如果方法没传入 access_token 或者 openId，就从配置中获取

        if (empty($arg['access_token']) || empty($arg['openid'])) {
            $this->array = [
                'oauth_consumer_key' => (int) $this->readConfig('appid'),
                'access_token' => $this->get('access_token'),
                'openid' => $this->get('openid'),
            ];
        } else {
            $this->array = [
                'oauth_consumer_key' => (int) $this->readConfig('appid'),
                'access_token' => $arg['access_token'],
                'openid' => $arg['openid'],
            ];
        }

        // 如果APIMap不存在相应的api

        if (!array_key_exists($name, self::API_MAP)) {
            throw new QQError(1);
        }

        // 从APIMap获取api相应参数

        $baseUrl = self::API_MAP[$name][0];
        $argsList = self::API_MAP[$name][1];

        // 获取请求方法,若参数不存在则为 get 方法

        $method = isset($this->APIMap[$name][2]) ? self::API_MAP[$name][2] : 'GET';

        if ($name === 'get_tenpay_addr') {
            // 对于 get_tenpay_addr，特殊处理，php json_decode 对\xA312 此类字符支持不好

            $response = $this->jsonToArray($this->applyAPI($arg[0], $argsList, $baseUrl, $method));
        } else {
            $response = json_decode($this->applyAPI($arg[0], $argsList, $baseUrl, $method), true);
        }

        if ($response['ret'] !== 0) {
            throw new QQError((int) $response->ret, $response->msg);
        }

        return $response;
    }

    /**
     * 获得 access_token.
     *
     * @param void
     *
     * @return string 返加access_token
     */
    public function getAccessToken()
    {
        return $this->config->get('access_token');
    }

    /**
     * 简单实现 json 到 php 数组转换功能
     *
     * @param string $json
     * @return array
     */
    private function jsonToArray(string $json)
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
