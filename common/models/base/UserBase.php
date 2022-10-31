<?php

namespace common\models\base;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id id
 * @property string $name 姓名
 * @property int $mobile 手机号
 * @property string $token token
 * @property string $password 密码
 * @property int $status 状态(0-禁用；1-正常)
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class UserBase extends \common\extensions\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mobile', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['token'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => '姓名',
            'mobile' => '手机号',
            'token' => 'token',
            'password' => '密码',
            'status' => '状态(0-禁用；1-正常)',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
        ];
    }
}
