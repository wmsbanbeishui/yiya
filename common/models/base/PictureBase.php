<?php

namespace common\models\base;

use Yii;

/**
 * This is the model class for table "picture".
 *
 * @property int $id id
 * @property string $name 名称
 * @property string $picture 图片
 * @property int $is_push 是否推送到首页
 * @property string $create_time 添加时间
 */
class PictureBase extends \common\extensions\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'picture';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_push'], 'integer'],
            [['create_time'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['picture'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => '名称',
            'picture' => '图片',
            'is_push' => '是否推送到首页',
            'create_time' => '添加时间',
        ];
    }
}
