<?php

namespace common\services;

use common\helpers\Helper;
use common\models\table\Customer;
use Yii;
use yii\helpers\ArrayHelper;

class UserService
{
    /**
     * 获取会员信息
     * @return array
     */
    public static function getCustInfo()
    {
        $cust_id = Yii::$app->user->id;

        $cust_info = Customer::find()
            ->select(['id', 'code', 'name', 'mobile', 'open_id', 'token', 'avatar',
                'gender', 'birthday', 'id_card_no', 'gasoline_balance',
                'diesel_balance', 'points', 'growth', 'create_time'])
            ->where(['id' => $cust_id])
            ->asArray()
            ->one();

        $cust_info['full_avatar'] = Helper::getImageUrl($cust_info['avatar']);

        $cust_info['gasoline_balance'] = sprintf('%0.2f', $cust_info['gasoline_balance'] / 100);
        $cust_info['diesel_balance'] = sprintf('%0.2f', $cust_info['diesel_balance'] / 100);
        $cust_info['total_balance'] = $cust_info['gasoline_balance'] + $cust_info['diesel_balance'];

        // 会员天数
        $cust_info['cust_days'] = Helper::getDiffDays(date('Y-m-d', strtotime($cust_info['create_time'])), date('Y-m-d')) ?: 1;

        // 会员等级
        $level_info = self::getCustLevel($cust_info['growth']);
        $cust_info['cust_level'] = $level_info['level'];
        $cust_info['level_percent'] = $level_info['percent'];

        // 会员二维码图片
        $qrcode_url = self::getQrcode($cust_id);
        $cust_info['qrcode'] = Helper::getImageUrl($qrcode_url);

        return [
            'code' => 0,
            'msg' => '',
            'data' => $cust_info
        ];
    }

    /**
     * 计算会员等级
     * @param $growth
     * @return array
     */
    public static function getCustLevel($growth)
    {
        if ($growth <= 50) {
            $level = '白银会员';
            $percent = intval($growth / 50 * 100);
        } elseif ($growth > 50 && $growth <= 1000) {
            $level = '黄金会员';
            $percent = intval($growth / 1000 * 100);
        } elseif ($growth > 1000 && $growth <= 5000) {
            $level = '白金会员';
            $percent = intval($growth / 5000 * 100);
        } else {
            $level = '钻石';
            $percent = 100;
        }

        return [
            'level' => $level,
            'percent' => $percent
        ];
    }

    /**
     * 修改手机号 - 第一步
     * @param $code
     * @return array
     */
    public static function updateMobileOne($code)
    {
        $user_info = Customer::findOne(Yii::$app->user->id);

        if (empty($user_info)) {
            return [
                'code' => 101,
                'msg' => '该会员不存在'
            ];
        }
        $codeCheck = CodeMsgService::checkCode($user_info->mobile, $code, $type = 3);

        if (!$codeCheck) {
            return [
                'code' => 102,
                'msg' => '验证码不正确'
            ];
        }

        // 缓存一个key值，第二步用到
        $redis = Yii::$app->redis;
        $key = Helper::randString($len = 8);
        $redis->set($key, json_encode([
            'cust_id' => Yii::$app->user->id,
            'key' => $key,
        ]));
        $redis->expire($key, 180);

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'key' => $key
            ]
        ];
    }

    /**
     * 修改手机号 - 第二步
     * @param $mobile
     * @param $code
     * @param $key
     * @return array
     */
    public static function updateMobileTwo($mobile, $code, $key)
    {
        $cust_id = Yii::$app->user->id;
        $user_info = Customer::findOne($cust_id);

        if (empty($user_info)) {
            return [
                'code' => 201,
                'msg' => '该会员不存在'
            ];
        }

        // 检测手机号是否被占用
        $exists = Customer::find()->where(['mobile' => $mobile, 'status' => 1])->exists();
        if ($exists) {
            return [
                'code' => 202,
                'msg' => '新的手机号已被占用'
            ];
        }

        if ($mobile == $user_info->mobile) {
            return [
                'code' => 203,
                'msg' => '新的手机号与原手机号相同'
            ];
        }

        $redis = Yii::$app->redis;
        $str = $redis->get($key);
        $validate_info = json_decode($str, true);

        if (ArrayHelper::getValue($validate_info, 'cust_id') != $cust_id ||
            ArrayHelper::getValue($validate_info, 'key') != $key
        ) {
            return [
                'code' => 204,
                'msg' => '参数错误，重试第一步'
            ];
        }

        $codeCheck = CodeMsgService::checkCode($mobile, $code, $type = 3);

        if (!$codeCheck) {
            return [
                'code' => 205,
                'msg' => '验证码不正确'
            ];
        }

        $user_info->mobile = $mobile;
        if (!$user_info->save()) {
            return [
                'code' => 206,
                'msg' => current($user_info->getFirstErrors())
            ];
        }

        return [
            'code' => 0,
            'msg' => '修改成功',
        ];
    }

    /**
     * 修改支付密码 - 第一步
     * @param $code
     * @return array
     */
    public static function updatePwdOne($code)
    {
        $user_info = Customer::findOne(Yii::$app->user->id);

        if (empty($user_info)) {
            return [
                'code' => 301,
                'msg' => '该会员不存在'
            ];
        }
        $codeCheck = CodeMsgService::checkCode($user_info->mobile, $code, $type = 2);

        if (!$codeCheck) {
            return [
                'code' => 302,
                'msg' => '验证码不正确'
            ];
        }

        // 缓存一个key值，第二步用到
        $redis = Yii::$app->redis;
        $key = Helper::randString($len = 8);
        $redis->set($key, json_encode([
            'cust_id' => Yii::$app->user->id,
            'key' => $key,
        ]));
        $redis->expire($key, 180);

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'key' => $key
            ]
        ];
    }

    /**
     * 修改支付密码 - 第二步
     * @param $new_pwd
     * @param $re_pwd
     * @param $key
     * @return array
     * @throws \yii\base\Exception
     */
    public static function updatePwdTwo($new_pwd, $re_pwd, $key)
    {
        $cust_id = Yii::$app->user->id;
        $user_info = Customer::findOne($cust_id);

        if (empty($user_info)) {
            return [
                'code' => 401,
                'msg' => '该会员不存在'
            ];
        }

        // 检测密码是否为6位数字
        if (!preg_match("/^\d{6}$/", $new_pwd)) {
            return [
                'code' => 401,
                'msg' => '请输入一个六位数字的密码'
            ];
        }

        // 检测两次密码是否一致
        if ($new_pwd != $re_pwd) {
            return [
                'code' => 402,
                'msg' => '新密码与确认密码不一致'
            ];
        }

        $redis = Yii::$app->redis;
        $str = $redis->get($key);
        $validate_info = json_decode($str, true);

        if (ArrayHelper::getValue($validate_info, 'cust_id') != $cust_id ||
            ArrayHelper::getValue($validate_info, 'key') != $key
        ) {
            return [
                'code' => 403,
                'msg' => '参数错误，重试第一步'
            ];
        }


        $user_info->pay_password = PasswordService::setPassword($new_pwd);
        if (!$user_info->save()) {
            return [
                'code' => 404,
                'msg' => current($user_info->getFirstErrors())
            ];
        }

        return [
            'code' => 0,
            'msg' => '修改成功',
        ];
    }

    /**
     * 获取会员二维码
     * @param $cust_id
     * @return string
     */
    public static function getQrcode($cust_id)
    {
        require_once Yii::getAlias('@common/WxPay/phpqrcode/phpqrcode.php');

        $cust_info = Customer::findOne($cust_id);

        // 如果存在则直接返回
        if ($cust_info->qrcode_url) {
            return $cust_info->qrcode_url;
        }

        $data = urlencode($cust_info->code);

        $path = '/image/qrcode/' . date('Ymd') . '/';
        $dir = Yii::getAlias('@upload') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }
        $file_name = $cust_id . date('Ymd_His') . mt_rand(100, 999) . '.png';

        \QRcode::png($data, $dir . $file_name, $level = QR_ECLEVEL_L, $size = 5, $margin = 4);

        $qrcode_url = $path . $file_name;
        Customer::updateAll(['qrcode_url' => $qrcode_url], ['id' => $cust_id]);

        return $path . $file_name;
    }
}