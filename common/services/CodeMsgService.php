<?php

namespace common\services;

use common\helpers\Helper;
use common\models\table\CodeMsg;
use common\validator\MobileValidator;
use Yii;

class CodeMsgService
{
    /**
     * 当天手机号获取验证码次数
     * @param $mobile
     * @return int|string
     */
    public static function getTodayCountByMobile($mobile)
    {
        $time = strtotime('today');
        $count = CodeMsg::find()->where([
            'AND',
            ['mobile' => $mobile],
            ['>=', 'create_time', $time]
        ])->count();
        return $count;
    }

    /**
     * 当天IP获取验证码次数
     * @param string|null $ip
     * @return int|string
     */
    public static function getTodayCountByIp($ip = null)
    {
        if (!$ip) {
            $ip = Yii::$app->getRequest()->getRemoteIP();
        }
        $time = strtotime('today');
        $count = CodeMsg::find()->where([
            'AND',
            ['from_ip' => $ip],
            ['>=', 'create_time', $time]
        ])->count();
        return $count;
    }

    /**
     * 根据手机号的code查询是否可用
     * @param $mobile
     * @param $code
     * @param int $type
     * @param null $time
     * @return bool
     */
    public static function checkCode($mobile, $code, $type = 1, $time = null)
    {
        $time = $time ?: time();
        return CodeMsg::find()->where([
            'AND',
            ['mobile' => $mobile],
            ['code' => $code],
            ['type' => $type],
            ['>=', 'deadline', $time],
            ['<=', 'create_time', $time]
        ])->exists();
    }

    /**
     * 验证码发送
     * @param $mobile
     * @param int $type
     * @return array
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public static function sendCode($mobile, $type = 1)
    {
        $request = Yii::$app->getRequest();
        $ip = $request->getRemoteIP();
        $time = time();

        $mobileValidator = new MobileValidator();
        if (!$mobileValidator->validate($mobile)) {
            return [
                'code' => 101,
                'msg' => '手机号格式错误'
            ];
        }

        if (!YII_ENV_DEV) {
            $referrerMatch = false;
            $referrerUrl = $request->getReferrer();
            if ($referrerUrl) {
                $referrerUrlInfo = parse_url($referrerUrl);
                if ($referrerUrlInfo) {
                    $referrerHost = $referrerUrlInfo['host'];
                    $corsWhiteHosts = Helper::getParam('cors_white_hosts');
                    foreach ($corsWhiteHosts as $corsWhiteHost) {
                        if (strpos($referrerHost, $corsWhiteHost) !== false) {
                            $referrerMatch = true;
                        }
                    }
                }
            }
            if (!$referrerMatch) {
                return [
                    'code' => 102,
                    'msg' => '系统繁忙，请稍后重试'
                ];
            }

            // 每日总量限制
            $today = strtotime('today');
            $smsDailyAllLimit = Helper::getParam('sms_daily_all_limit');
            $todayAllCount = CodeMsg::find()->where(['>=', 'create_time', $today])->count();
            if ($todayAllCount > $smsDailyAllLimit) {
                return [
                    'code' => 103,
                    'msg' => '系统繁忙，请稍后重试'
                ];
            }

            // 每日次数限制
            $smsDailySingleLimit = Helper::getParam('sms_daily_single_limit');
            $ipTodayCount = self::getTodayCountByIp();
            if ($ipTodayCount > $smsDailySingleLimit) {
                return [
                    'code' => 104,
                    'msg' => '系统繁忙，请稍后重试'
                ];
            }
            $mobileTodayCount = self::getTodayCountByMobile($mobile);
            if ($mobileTodayCount > $smsDailySingleLimit) {
                return [
                    'code' => 105,
                    'msg' => '系统繁忙，请稍后重试'
                ];
            }

            $smsSingleLimit = Helper::getParam('sms_single_limit');
            $ipAllCount = CodeMsg::find()->where(['from_ip' => $ip])->count();
            $mobileAllCount = CodeMsg::find()->where(['mobile' => $mobile])->count();
            if ($ipAllCount >= $smsSingleLimit) {
                // todo 发送微信提醒
            }

            if ($mobileAllCount >= $smsSingleLimit) {
                // todo 发送微信提醒
            }
        }

        // 统一定为4位数字
        $code = rand(1, 9) . rand(1, 9) . rand(1, 9) . rand(1, 9);

        $model = new CodeMsg();
        $model->type = $type;
        $model->mobile = $mobile;
        $model->code = $code;
        $model->from_ip = $ip;
        $model->deadline = $time + 180; // 3分钟后过期
        $model->create_time = $time;

        if (!$model->save()) {
            return [
                'code' => 106,
                'msg' => current($model->getFirstErrors())
            ];
        }

        // 不开启发送的，直接返回验证码
        $is_send = Helper::getParam('send_code');
        if (!$is_send) {
            return [
                'code' => 0,
                'msg' => '',
                'data' => $code
            ];
        }

        // 阿里云发送
        $sms_tpl_code = 'SMS_205445421';
        $return = SmsService::sendSMS($mobile, '一丫相册', $sms_tpl_code, ['code' => $code]);
        //var_dump($return);exit;
        if (!isset($return['Message'])) {
            return [
                'code' => 107,
                'msg' => '发送失败，请稍后重试！'
            ];
        }

        if ($return['Message'] == 'OK') {
            $model->status = 1;
            $model->notify_time = date('Y-m-d H:i:s');
            $model->save();
            return [
                'code' => 0,
                'msg' => '发送成功'
            ];
        } else {
            return [
                'code' => 108,
                'msg' => '发送失败，请稍后重试！'
            ];
        }
    }
}
