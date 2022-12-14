<?php

namespace common\extensions;

use common\helpers\Helper;
use common\models\table\AdminLog;
use common\models\table\OrderLog;
use Yii;
use yii\db\ActiveRecord as YiiActiveRecord;

/**
 * @method ActiveQuery hasMany($class, array $link) see [[BaseActiveRecord::hasMany()]] for more info
 * @method ActiveQuery hasOne($class, array $link) see [[BaseActiveRecord::hasOne()]] for more info
 */
class ActiveRecord extends YiiActiveRecord
{
    /**
     * @return ActiveQuery
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * 清空表
     * @return int
     */
    public static function truncateTable()
    {
        return static::getDb()
            ->createCommand()
            ->truncateTable(static::tableName())
            ->execute();
    }

    /**
     * 获取第一条错误
     * @return string
     */
    public function getFirstErrorString()
    {
        if ($this->hasErrors()) {
            return array_values($this->getErrors())[0][0];
        }
        return '';
    }

}
