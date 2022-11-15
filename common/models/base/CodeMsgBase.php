<?php

namespace common\models\base;

use Yii;

/**
 * This is the model class for table "code_msg".
 *
 * @property int $id ID
 * @property int $mobile 手机号
 * @property string $code 验证码
 * @property int $type 类型(1-登录；2-修改支付密码；3-修改手机号；4-绑定油卡；5-解绑油卡)
 * @property string $error_msg 发送失败说明
 * @property int $deadline 失效时间
 * @property string $from_ip IP地址
 * @property int $create_time 创建时间
 * @property int $status 状态(0-未发送；1-已发送)
 * @property string $send_time 发送时间
 * @property string $notify_time 回调通知时间
 */
class CodeMsgBase extends \common\extensions\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'code_msg';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mobile', 'code', 'deadline', 'create_time'], 'required'],
            [['mobile', 'type', 'deadline', 'create_time', 'status'], 'integer'],
            [['send_time', 'notify_time'], 'safe'],
            [['code'], 'string', 'max' => 8],
            [['error_msg'], 'string', 'max' => 255],
            [['from_ip'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => '手机号',
            'code' => '验证码',
            'type' => '类型(1-登录；2-修改支付密码；3-修改手机号；4-绑定油卡；5-解绑油卡)',
            'error_msg' => '发送失败说明',
            'deadline' => '失效时间',
            'from_ip' => 'IP地址',
            'create_time' => '创建时间',
            'status' => '状态(0-未发送；1-已发送)',
            'send_time' => '发送时间',
            'notify_time' => '回调通知时间',
        ];
    }
}
