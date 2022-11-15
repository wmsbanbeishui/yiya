<?php

namespace common\services;

use common\helpers\Helper;
use common\models\table\User;
use Yii;

class UserService
{
    /**
     * 获取用户信息
     * @return array
     */
    public static function getCustInfo()
    {
        $cust_id = Yii::$app->user->id;

        $cust_info = User::find()
            ->select(['id', 'name', 'mobile', 'open_id', 'token', 'avatar', 'create_time'])
            ->where(['id' => $cust_id])
            ->asArray()
            ->one();

        $cust_info['full_avatar'] = Helper::getImageUrl($cust_info['avatar']);

        return [
            'code' => 0,
            'msg' => '',
            'data' => $cust_info
        ];
    }
}