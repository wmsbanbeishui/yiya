<?php

namespace common\helpers;

use Yii;

class RequestHelper
{
    public static function data($key, $default = '')
    {
        $json_str = file_get_contents('php://input');
        $arr = json_decode($json_str, true);
        if (isset($arr[$key])) return $arr[$key];


        $data = Yii::$app->request->get($key, null);
        if ($data !== null) {
            return $data;
        }
        return $default;
    }

    public static function post()
    {
        $json_str = file_get_contents('php://input');
        if (empty($json_str)) return [];
        $arr = json_decode($json_str, true);
        return $arr;
    }

    public static function postList($key)
    {
        $data = self::post();
        // return $data;
        if (empty($data)) return false;

        if (!is_array($key)) {
            if (isset($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
            return false;
        } else {
            $dat = [];
            foreach ($key as $k) {
                if (!isset($data[$k]) || $data[$k] === '') {
                    return false;
                }
                $dat[] = $data[$k];
            }
            return $dat;
        }
    }

}
