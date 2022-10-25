<?php

namespace api\modules\v1\models\form;

use common\helpers\Helper;
use common\models\table\Customer;
use common\services\CodeMsgService;
use common\validator\MobileValidator;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $mobile;
    public $open_id;
    public $code;
    public $avatar;
    public $nick_name;
    public $gender;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile' => '手机号',
            'open_id' => 'openid',
            'code' => '验证码',
            'avatar' => '头像',
            'nick_name' => '昵称',
            'gender' => '性别',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'open_id'], 'required'],
            ['mobile', MobileValidator::className()],
            ['code', 'validateCode'],
            [['avatar', 'nick_name', 'gender'], 'safe']
        ];
    }

    public function validateCode($attribute, $params)
    {
        if (!$this->hasErrors() && isset($this->code)) {
            $codeCheck = CodeMsgService::checkCode($this->mobile, $this->code, $type = 1);
            if (!$codeCheck) {
                $this->addError($attribute, '验证码错误');
            }
        }
    }

    /**
     * @return array|bool|false
     * @throws \yii\db\Exception
     */
    public function login()
    {
        if ($this->hasErrors()) {
            return false;
        }

        // 处理姓名
        $tmp_name = '***' . substr($this->mobile, -4);

        $user = Customer::find()->where(['mobile' => $this->mobile, 'status' => 1])->one();

        $validate = true;
        $transaction = Yii::$app->getDb()->beginTransaction();

        try {
            if ($user) { // 如果存在，更新登录

                // 如果该 mobile 用户的 openid 与 当前的 openid 不一致，则先清空当前 openid 的用户的 openid，再更新该 mobile 用户的 openid
                if ($user->open_id != $this->open_id) {
                    Customer::updateAll(['open_id' => null], ['open_id' => $this->open_id]);
                    $user->open_id = $this->open_id;
                }

                // 如果当前用户没有昵称等参数，就做更新操作
                if (!$user->name) $user->name = $this->nick_name ?: $tmp_name;

                if (!$user->gender) $user->gender = $this->gender ?: $user->gender;

                if (!$user->avatar) $user->avatar = $this->avatar ?: '/image/avatar/default.png';

                if (!$user->save()) {
                    $validate = false;
                    Helper::fLogs($user->getFirstErrors(), 'login_error.log');
                }

            } else { // 如果不存在，注册登录
                // 先将当前 openid 的用户的 openid 更新为null，再做注册登录操作
                Customer::updateAll(['open_id' => null], ['open_id' => $this->open_id]);

                $user = new Customer();
                $user->code = Helper::getOrderCode();
                $user->mobile = $this->mobile;
                $user->open_id = $this->open_id;
                $user->token = Helper::uuid();
                $user->name = $this->nick_name ?: $tmp_name;
                $user->avatar = $this->avatar ?: '/image/avatar/default.png';
                $user->gender = $this->gender ?: '0';

                if (!$user->save()) {
                    $validate = false;
                }
            }

            if ($validate) {
                $transaction->commit();
                return $user->login();

            } else {
                $transaction->rollBack();
                $this->addError('mobile', '登录失败，请重试');
                return false;
            }
        } catch (\Exception $exception) {
            $transaction->rollBack();
            $this->addError('mobile', '登录失败，请重试');
            return false;
        }
    }

    /**
     * 手机号 + openid + （code） 登录接口（可以带上昵称、头像、性别）
     *      先将当前 openid 的用户的 openid 更新为null，再做更新注册登录操作
     *      用选择的手机号去找用户
     *          如果有，更新登录，返回用户信息
     *              1. 将 openid 写入
     *              2. 姓名、昵称、头像、性别，是否有值，没有值，则做更新操作
     *          如果没有，注册登录，返回用户信息
     *              1. 重新注册新的用户
     *
     * 之前的 手机号 + openid 登录接口 和 手机号 + 验证码 登录接口 废弃了
     * 因为，后台添加的用户没有 openid，用手机号 + 验证码 登录后，依然没有openid，影响支付操作
     */
}