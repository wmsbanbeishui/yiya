<?php

namespace api\modules\v1\controllers;

use common\services\CodeMsgService;
use api\modules\v1\controllers\base\BaseController;
use Yii;

class SmsController extends BaseController
{
    /**
     * 需要登录的路由
     * @return array
     */
    protected static function authAction()
    {
        return [];
    }

    /**
     * 不需要登录的路由
     * @return array|string[]
     */
    protected static function normalAction()
    {
        return ['send-code'];
    }

    /**
     * 发送验证码
     * @return array
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    public function actionSendCode()
    {
        $request = Yii::$app->getRequest();
        $mobile = $request->post('mobile');
        $type = $request->post('type'); // 1-登录；2-修改支付密码；3-修改手机号；4-绑定油卡；5-解绑油卡

        return CodeMsgService::sendCode($mobile, $type);
    }
}