<?php

namespace common\models\base;

use Yii;

/**
 * This is the model class for table "picture".
 *
 * @property int $id id
 * @property string $date 日期
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
            [['date', 'create_time'], 'safe'],
            [['is_push'], 'integer'],
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
            'date' => '日期',
            'name' => '名称',
            'picture' => '图片',
            'is_push' => '是否推送到首页',
            'create_time' => '添加时间',
        ];
    }
}
