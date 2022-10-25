<?php

namespace common\services;

use common\helpers\Helper;
use common\models\table\WxTplMsg;
use Yii;

class WxService
{
    /**
     * 获取接口调用凭证
     * @return array|int|mixed|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public static function getAccessToken()
    {
        $access_token = Helper::redis_get('wx_access_token');

        if (!$access_token) {
            $url = 'https://api.weixin.qq.com/cgi-bin/token';
            $query_data = [
                'grant_type' => 'client_credential',
                'appid' => Helper::get_wx_cfg('app_id'),
                'secret' => Helper::get_wx_cfg('app_secret'),
            ];

            $return = Helper::curl($url, $query_data);
            if (!empty($return['errno']) || !empty($return['errcode'])) {
                Helper::fLogs($return, 'wx_access_token.log');
                return [
                    'code' => 101,
                    'msg' => '获取access_token出错',
                    'error' => $return,
                ];
            }

            $access_token = $return['access_token'];

            Helper::redis_set('wx_access_token', $access_token, 3600);
        }

        return $access_token;
    }

    /**
     * 发布小程序订阅消息
     * @param $data
     * @return array|int|mixed|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public static function sendMessage($data)
    {
        $miniprogram_state = Helper::getParam('miniprogram_state');

        $template_map = self::getTemplateMap();

        if (!isset($data['msg_type']) || !isset($template_map[$data['msg_type']])) {
            return [
                'code' => 101,
                'msg' => 'Invalid msg_type/wx_tpl',
            ];
        }

        $template_data = $template_map[$data['msg_type']];
        $template_id = $template_data['template_id'];

        foreach ($template_data['data'] as $key => $value) {
            if (!isset($data['data']) || empty($data['data'][$key])) {

                if (substr($key, 0, 6) == 'amount') {
                    $value = '￥' . "{$data[$value]}";
                } else {
                    $value = "{$data[$value]}";
                }

                $data['data'][$key] = [
                    'value' => $value
                ];
            }
        }

        // 允许微信推送消息链接为空
        if (!isset($data['page']) || empty($data['page'])) {
            $data['page'] = $template_data['page'];
        }

        // open_id  获取
        $open_id = isset($data['open_id']) ? $data['open_id'] : '';
        if (empty($open_id)) {
            if (isset($data['user_id']) && !empty($data['user_id'])) {
                // 发送给用户
                $user = User::findOne($data['user_id']);
                if (!$user) {
                    return [
                        'code' => 102,
                        'msg' => 'Invalid user',
                    ];
                }
                $open_id = $user->open_id;
            }
        }
        if (empty($open_id)) {
            return [
                'code' => 103,
                'msg' => 'Invalid receiver',
            ];
        }

        $access_token = self::getAccessToken();
        if (is_array($access_token)) {
            return $access_token;
        }

        // 内容
        $message = [
            'touser' => $open_id,
            'template_id' => $template_id,
            'page' => $data['page'],
            'data' => $data['data'],
            'miniprogram_state' => $miniprogram_state
        ];

        // 保存记录
        $wx_tpl_msg = new WxTplMsg();
        $wx_tpl_msg->user_id = strval($data['user_id']) ?: '0';
        $wx_tpl_msg->template_id = $template_id;
        $wx_tpl_msg->wx_open_id = $open_id;
        $wx_tpl_msg->url = $data['page'];
        $wx_tpl_msg->content = Helper::json_output($data['data'], false);
        $wx_tpl_msg->status = 2;
        if (!$wx_tpl_msg->save()) {
            $error = $wx_tpl_msg->getErrors();
            Helper::fLogs([$_SERVER, $error], 'wxmsg_save_error.log');
        }

        // 发送
        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $access_token;
        $return = Helper::curl($url, json_encode($message), 'post');

        if (!empty($return['errcode'])) {
            if ($return['errcode'] == 43101) {
                $wx_tpl_msg->status = 5; // 拒收
            } else {
                $wx_tpl_msg->status = 4; // 失败
                Helper::fLogs([$_SERVER, $message, $return], 'wxmsg_send_error.log');
            }

        } else {
            $wx_tpl_msg->status = 3; // 成功
            $wx_tpl_msg->send_time = date('Y-m-d H:i:s');
            $wx_tpl_msg->wxmsg_id = strval($return['msgid']);
        }

        if (!$wx_tpl_msg->save()) {
            $error = $wx_tpl_msg->getErrors();
            Helper::fLogs([$_SERVER, $error], 'wxmsg_update_error.log');
        }

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'status' => $wx_tpl_msg->status,
                'send_time' => $wx_tpl_msg->send_time
            ],
        ];
    }

    /**
     * 获取消息模板信息
     * @return \string[][]
     */
    public static function getTemplateMap()
    {
        return [
            // 代理商申请审核通知
            'agent_audit_notify' => [
                'template_id' => 'DEazSNTeVVpZW6UI5Q52SeuWYH_i3AvhTjNBqJod5gY',
                'page' => '/pages/tabbar/user/index',
                'data' => [
                    'thing1' => 'type', // 审核类型
                    'thing15' => 'name', // 申请名称
                    'thing4' => 'result', // 审核结果
                    'thing5' => 'remark', // 备注信息
                    'date7' => 'audit_time', // 审核时间
                ]
            ],
            'shoot_check_notify' => [
                'template_id' => '',
                'page' => '/pages/tabbar/order/index',
                'thing1' => 'type', // 审核类型
                'thing15' => 'name', // 申请名称
                'thing4' => 'result', // 审核结果
                'thing5' => 'remark', // 备注信息
                'date7' => 'audit_time' // 审核时间
            ],
            'user_pay_order' => [
                'template_id' => '',
                'page' => '/pages/tabbar/order/index',
                'thing1' => 'type', // 审核类型
                'thing15' => 'name', // 申请名称
                'thing4' => 'result', // 审核结果
                'thing5' => 'remark', // 备注信息
                'date7' => 'audit_time' // 审核时间
            ],
        ];
    }

    /**
     * 获取小程序二维码
     * @param $params
     * @return array|bool|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public static function getXcxCode($params)
    {
        $access_token = self::getAccessToken();
        if (is_array($access_token)) {
            return [
                'code' => 201,
                'msg' => '操作失败，请稍后重试'
            ];
        }
        $params = json_encode($params);

        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token;
        $result = self::getCurl($url, $params);

        if (is_array($result)) {
            return $result;
        }

        $path = '/image/xcx_code/' . date('Ymd');
        $extension = 'png';
        $picture = self::saveImage($path, $extension, $result);


        return [
            'code' => 0,
            'msg' => '',
            'data' => $picture
        ];
    }

    /**
     * 获取小程序二维码，主要是微信的该接口返回的是图片buff，不是json
     * @param $url
     * @param array $data
     * @return array|bool|string
     */
    public static function getCurl($url, $data = [])
    {
        $method = 'post';

        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
        ];
        if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'NMC_BETA') {
            $options[CURLOPT_USERAGENT] = $_SERVER['HTTP_USER_AGENT'];
        }
        if ($method == 'post' && $data) {
            $options[CURLOPT_POSTFIELDS] = $data;
            if (!is_array($data)) {
                $options[CURLOPT_HTTPHEADER] = ['Content-Type: text/plain'];
            }
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if ($response === false) {
            $result = [
                'code' => curl_errno($ch),
                'msg' => curl_error($ch),
            ];
            return $result;
        }

        $result = json_decode($response, true);
        if ($result) {
            return [
                'code' => $result['errcode'],
                'msg' => $result['errmsg'],
            ];
        }

        return $response;
    }

    /**
     * 保存小程序二维码图片
     * @param $path
     * @param $extension
     * @param $buff
     * @return string
     */
    public static function saveImage($path, $extension, $buff)
    {
        $file_name = sprintf('%s_%s.%s', date('Ymd_His'), mt_rand(100, 999), $extension);
        $dir = Yii::getAlias('@upload') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }
        $file_path = $dir . '/' . $file_name;
        file_put_contents($file_path, $buff);

        return Helper::getImageUrl($path . '/' . $file_name);
    }
}
