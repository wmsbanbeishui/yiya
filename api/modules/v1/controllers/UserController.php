<?php

namespace api\modules\v1\controllers;

use common\helpers\Helper;
use common\services\UserService;
use common\models\table\Customer;
use common\validator\IdCardValidator;
use api\modules\v1\models\form\LoginForm;
use api\modules\v1\controllers\base\BaseController;
use Yii;

class UserController extends BaseController
{
    protected static function authAction()
    {
        return ['get-cust-info', 'update-info', 'update-mobile-one', 'update-mobile-two',
            'update-pwd-one', 'update-pwd-two', 'get-qrcode'];
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

        // $user = Customer::findOne(['open_id' => $open_id]);

        $user = Customer::find()->where(['open_id' => $open_id, 'status' => 1])->one();

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
     * 获取会员信息
     * @return array
     */
    public function actionGetCustInfo()
    {
        return UserService::getCustInfo();
    }

    /**
     * 更新会员信息
     * @return array
     */
    public function actionUpdateInfo()
    {
        $request = Yii::$app->request;
        $avatar = $request->post('avatar');
        $name = $request->post('name');
        $gender = $request->post('gender');
        $birthday = $request->post('birthday');
        $id_card_no = $request->post('id_card_no');

        $cust_info = Customer::findOne(Yii::$app->user->id);

        if ($avatar) {
            $cust_info->avatar = $avatar;
        }

        if ($name) {
            $cust_info->name = htmlspecialchars($name);
        }

        if ($gender) {
            if (in_array($gender, [0, 1, 2])) {
                $cust_info->gender = $gender;
            } else {
                return [
                    'code' => 301,
                    'msg' => '性别无效'
                ];
            }
        }

        if ($birthday) {
            if ((date('Y-m-d', strtotime($birthday)) == $birthday)) {
                $cust_info->birthday = $birthday;
            } else {
                return [
                    'code' => 302,
                    'msg' => '生日无效'
                ];
            }
        }

        if ($id_card_no) {
            $idCardValidator = new IdCardValidator();
            if ($idCardValidator->validate($id_card_no)) {
                $cust_info->id_card_no = $id_card_no;
            } else {
                return [
                    'code' => 303,
                    'msg' => '身份证号码无效'
                ];
            }
        }

        if (!$cust_info->save()) {
            return [
                'code' => 304,
                'msg' => '更新失败'
            ];
        }

        return [
            'code' => 0,
            'msg' => '更新成功'
        ];
    }

    /**
     * 修改手机号 - 第一步
     * @return array
     */
    public function actionUpdateMobileOne()
    {
        $request = Yii::$app->request;
        $code = $request->post('code');

        return UserService::updateMobileOne($code);
    }

    /**
     * 修改手机号 - 第二步
     * @return array
     */
    public function actionUpdateMobileTwo()
    {
        $request = Yii::$app->request;
        $mobile = $request->post('mobile');
        $code = $request->post('code');
        $key = $request->post('key');

        return UserService::updateMobileTwo($mobile, $code, $key);
    }

    /**
     * 修改支付密码 - 第一步
     * @return array
     */
    public function actionUpdatePwdOne()
    {
        $request = Yii::$app->request;
        $code = $request->post('code');

        return UserService::updatePwdOne($code);
    }

    /**
     * 修改支付密码 - 第二步
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionUpdatePwdTwo()
    {
        $request = Yii::$app->request;
        $new_pwd = $request->post('new_pwd');
        $re_pwd = $request->post('re_pwd');
        $key = $request->post('key');

        return UserService::updatePwdTwo($new_pwd, $re_pwd, $key);
    }

    /**
     * 获取会员二维码信息
     * @return array
     */
    public function actionGetQrcode()
    {
        $cust_id = Yii::$app->user->id;

        $qrcode_url = UserService::getQrcode($cust_id);

        return [
            'code' => 0,
            'msg' => '',
            'data' => Helper::getImageUrl($qrcode_url)
        ];
    }
}