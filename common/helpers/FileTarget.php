<?php

namespace common\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\log\FileTarget as YiiFileTarget;

/**
 * 文件日志类(重写)
 */
class FileTarget extends YiiFileTarget
{

    /**
     * 写入日志文件
     */
    public function export()
    {
        $http_exception_list = [
            '\yii\web\BadRequestHttpException',
            '\yii\web\ConflictHttpException',
            '\yii\web\ForbiddenHttpException',
            '\yii\web\GoneHttpException',
            '\yii\web\MethodNotAllowedHttpException',
            '\yii\web\NotAcceptableHttpException',
            '\yii\web\NotFoundHttpException',
            '\yii\web\RangeNotSatisfiableHttpException',
            '\yii\web\ServerErrorHttpException',
            '\yii\web\TooManyRequestsHttpException',
            '\yii\web\UnauthorizedHttpException',
            '\yii\web\UnprocessableEntityHttpException',
            '\yii\web\UnsupportedMediaTypeHttpException',
        ];
        foreach ($http_exception_list as $http_exception) {
            if ($this->messages[0][0] instanceof $http_exception) {
                $this->logFile = str_replace('/app.log', '/app.' . $this->messages[0][0]->statusCode . '.log', $this->logFile);
            }
        }

        $ignore_http_error = [400, 401, 404, 405];
        $status_code = isset($this->messages[0][0]->statusCode) ? $this->messages[0][0]->statusCode : 0;
        if (Helper::getParam('send_error_report') && empty($GLOBALS['sent_error_report']) && !in_array($status_code, $ignore_http_error)) {
            $GLOBALS['sent_error_report'] = 1;
            $app_id = Yii::$app->id;
            $app_name = Yii::$app->name;

            $receiver = null;
            $assign_map = Helper::getParam('error_report_receiver');
            foreach ($assign_map as $key => $map) {
                if (in_array($app_id, $map)) {
                    $receiver = $key;
                    break;
                }
            }

            if (!$receiver) {
                $tmp = array_keys($assign_map);
                $receiver = reset($tmp);
            }
            unset($assign_map[$receiver]);
            $cc = array_keys($assign_map);

            $sender = ArrayHelper::getValue(Yii::$app->components, 'mailer.transport.username');
            $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";

            $messages = $this->messages[0][0];
            $messages = is_object($messages) ? $messages->getMessage() : $messages;

            // 处理重复通知
            $notify = true;
            $cache_messages_list = [
                '[104] Too many connections',
                '[429] 服务器繁忙，请稍后再试'
            ];

            foreach ($cache_messages_list as $idx => $cache_messages) {
                if (strpos($messages, $cache_messages) !== false) {
                    $cache_count_key = 'notify_count_'.$idx;
                    $notify_count = Helper::redis_get($cache_count_key);
                    $notify_count++;
                    Helper::redis_set($cache_count_key, $notify_count);
                    if (($notify_count == 1) || ($notify_count % 100 == 0)) {
                        // 记录最后一次发送时间，十分钟内相同错误不再重复发送
                        $timeout = 600;
                        $last_sent_key = 'notify_latest_'.$idx;
                        $time = isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : time();
                        if (Helper::redis_get($last_sent_key) > $time - $timeout) {
                            $notify = false;
                            break;
                        }
                        Helper::redis_set($last_sent_key, $time, $timeout);
                        $messages = $messages.', 已发生'.$notify_count.'次';
                    } else {
                        $notify = false;
                    }
                    break;
                }
            }

            // 处理重复通知
            if ($notify) {
                $url = null;
                if (isset($_SERVER['HTTP_HOST'])) {
                    $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                }
                Yii::$app->mailer->compose()
                    ->setFrom([$sender => Yii::$app->name])
                    ->setTo($receiver)
                    ->setCc($cc)
                    //->setSubject('['.strtoupper($app_id).' - 线上错误报告] '.$messages)
                    ->setSubject('['. $app_name. $app_id. '线上错误报告] '.$messages)
                    ->setHtmlBody(date('Y-m-d H:i:s')."<br><a href=$url>$url</a><hr><pre>$text</pre>")
                    ->send();
            }

        }

        parent::export();
    }

}
