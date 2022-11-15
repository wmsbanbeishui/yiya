<?php

namespace api\modules\v1\controllers;

use common\models\table\User;
use common\services\UserService;
use api\modules\v1\models\form\LoginForm;
use api\modules\v1\controllers\base\BaseController;
use Yii;

class UserController extends BaseController
{
    protected static function authAction()
    {
        return ['get-cust-info'];
    }

    protected static function normalAction()
    {
        return ['check-openid', 'login'];
    }

    /**
     * 微信OpenId查找用户（前端用来判断是否拉起授权）
     * @return array
     */
    public function actionCheckOpenid()
    {
        $request = Yii::$app->request;
        $open_id = $request->post('openid');

        $user = User::find()->where(['open_id' => $open_id, 'status' => 1])->one();

        if (!$user) {
            return [
                'code' => 101,
                'msg' => '用户不存在'
            ];
        }

        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }

    /**
     * 小程序登录（手机号 + openid + （code））
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public function actionLogin()
    {
        $request = Yii::$app->getRequest();
        $form = new LoginForm();
        $form->setAttributes($request->post());

        $user_data = false;

        if ($form->validate()) {
            $user_data = $form->login();
        }

        if ($user_data !== false) {
            return [
                'code' => 0,
                'msg' => '登录成功',
                'data' => $user_data
            ];
        } else {
            return [
                'code' => 201,
                'msg' => current($form->getFirstErrors())
            ];
        }
    }

    /**
     * 获取用户信息
     * @return array
     */
    public function actionGetCustInfo()
    {
        return UserService::getCustInfo();
    }
}