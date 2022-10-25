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

    /**
     * 记录管理后台用户操作日志
     */
    public function afterSave($insert, $changedAttributes)
    {
        $table = $this->tableName();

        // 记录管理后台修改记录
        $track_list = ['admin'];
        $ignore_tables = ['admin_log', 'order_log'];
        $order_tables = ['order', 'order_detail', 'order_shoot', 'order_shoot_require', 'order_shoot_res'];
        if (in_array(Yii::$app->id, $track_list) && !in_array($table, $ignore_tables)) {
            $admin_id = Yii::$app->user->id;
            $data = $this->getAttributes();
            if ($insert) {
                $admin_log = new AdminLog();
                $admin_log->admin_id = $admin_id;
                $admin_log->table = $table;
                $admin_log->action = 1;
                $admin_log->record_id = isset($this->id) ? $this->id : null;
                $admin_log->new = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $success = $admin_log->save();
                if (!$success) {
                    $error = $admin_log->getErrors();
                    Helper::fLogs([$error, $_SERVER], 'admin_log_save_error.log');
                }

                // 如果是订单相关表，则记录订单操作日志
                if (in_array($table, $order_tables)) {
                    $order_log = new OrderLog();
                    $order_log->admin_id = $admin_id;
                    $order_log->table = $table;
                    $order_log->action = 1;
                    $order_log->record_id = isset($this->id) ? $this->id : null;
                    $order_log->new = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $success = $order_log->save();
                    if (!$success) {
                        $error = $order_log->getErrors();
                        Helper::fLogs([$error, $_SERVER], 'order_log_save_error.log');
                    }
                }

            } else {
                foreach ($changedAttributes as $key => $value) {
                    if (array_key_exists($key, $data) && $value != $data[$key]) {
                        $admin_log = new AdminLog();
                        $admin_log->admin_id = $admin_id;
                        $admin_log->table = $table;
                        $admin_log->action = 2;
                        $admin_log->field = $key;
                        $admin_log->record_id = isset($this->id) ? $this->id : null;
                        $admin_log->origin = strval($value);
                        $admin_log->new = strval($data[$key]);
                        $success = $admin_log->save();
                        if (!$success) {
                            $error = $admin_log->getErrors();
                            Helper::fLogs([$error, $_SERVER], 'admin_log_save_error.log');
                        }

                        // 如果是订单相关表，则记录订单操作日志
                        if (in_array($table, $order_tables)) {
                            $order_log = new OrderLog();
                            $order_log->admin_id = $admin_id;
                            $order_log->table = $table;
                            $order_log->action = 2;
                            $order_log->field = $key;
                            $order_log->record_id = isset($this->id) ? $this->id : null;
                            $order_log->origin = strval($value);
                            $order_log->new = strval($data[$key]);
                            $success = $order_log->save();
                            if (!$success) {
                                $error = $order_log->getErrors();
                                Helper::fLogs([$error, $_SERVER], 'order_log_save_error.log');
                            }
                        }
                    }
                }
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * 记录删除操作日志
     */
    public function afterDelete()
    {
        $table = $this->tableName();

        // 记录管理后台删除记录
        $track_list = ['admin'];
        $ignore_tables = ['admin_log', 'order_log'];
        if (in_array(Yii::$app->id, $track_list) && !in_array($table, $ignore_tables)) {
            $admin_id = Yii::$app->user->id;
            $data = $this->getAttributes();
            $admin_log = new AdminLog();
            $admin_log->admin_id = $admin_id;
            $admin_log->table = $table;
            $admin_log->action = 3;
            $admin_log->record_id = isset($this->id) ? $this->id : null;
            $admin_log->new = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $success = $admin_log->save();
            if (!$success) {
                $error = $admin_log->getErrors();
                Helper::fLogs([$error, $_SERVER], 'admin_log_save_error.log');
            }
        }

        return parent::afterDelete();
    }
}
