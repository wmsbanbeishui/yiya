<?php

namespace common\models\table;

use common\models\base\UserBase;
use common\validator\MobileValidator;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

class User extends UserBase implements IdentityInterface {

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'name' => '用户名',
            'mobile' => '手机号',
            'password' => '密码'
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return self::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return null;
    }

    public function validateAuthKey($authKey) {
        return $this->getAuthKey() == $authKey;
    }

    /**
     * 验证密码
     *
     * @param $password
     * @return bool
     */
    public function validatePassword($password) {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }

    /**
     * 加密密码
     *
     * @param $password
     * @return string
     * @throws \yii\base\Exception
     */
    public function encryptPassword($password) {
        return Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * 设置密码
     *
     * @param $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password) {
        $this->password = $this->encryptPassword($password);
    }
}
