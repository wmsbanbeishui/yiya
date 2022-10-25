<?php

namespace common\models\form;

use common\helpers\Helper;
use common\models\table\User;
use common\validator\MobileValidator;
use common\services\SmsRecordService;
use yii\base\Model;

class ResetPasswordForm extends Model
{
    public $mobile;
    public $code;
    public $password;
    public $re_password;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'code' => '验证码',
            'password' => '新密码',
            're_password' => '重复新密码',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'code', 'password', 're_password'], 'required'],
            ['mobile', MobileValidator::className()],
            ['re_password', 'compare', 'compareAttribute' => 'password'],
            ['code', 'validateCode'],
        ];
    }

    public function validateCode($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $codeCheck = SmsRecordService::checkCode($this->mobile, $this->code, $type = 3);
            if (!$codeCheck) {
                $this->addError($attribute, '验证码错误');
            }
        }
    }

    /**
     * @return array|false
     */
    public function reset()
    {
        if ($this->hasErrors()) {
            return false;
        }

        $user = User::find()->where(['mobile' => $this->mobile, 'status' => 1])->one();

        if (empty($user)) {
            $this->addError('mobile', '该手机号用户不存在');
            return false;
        }

        $user->password = $this->password;
        $user->token = Helper::uuid();

        if (!$user->save()) {
            $this->addError('mobile', '重置失败');
            return false;
        }

        return true;
    }
}
