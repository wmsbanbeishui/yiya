<?php

namespace common\extensions;

class ValueValidator
{
    public static function checkMobile($value)
    {
        return preg_match("/^1([38][0-9]|4[5-9]|5[0-35-9]|66|7[0-8]|9[8-9])\d{8}$/", $value);
    }

    // 6-12位，字母和数字组合
    public static function checkPassword($value)
    {
        return preg_match(" /^[a-zA-Z\d]{6,12}$/", $value);
    }

    //检测15到18位身份证号
    public static function checkIdCard($value)
    {
        return preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/", $value);
    }

    //检测银行卡号
    public static function checkBankNum($value)
    {
        return preg_match("/^(\d{16}|\d{19}|\d{17})$/", $value);
    }

    //检测是否url
    public static function checkUrl($url)
    {
        return preg_match("/^[hH][tT][tT][pP]([sS]?):\/\/(\S+\.)+\S{2,}$/", $url);
    }


    //检测大于0的正整数
    public static function checkNumber($number)
    {
        return preg_match("/^\+?[1-9]\d*$/", $number);
    }

    //增加参数过滤
    //用于扩展
    public static function getValue($value)
    {
        return $value;
    }

    //校验Token--后续扩展是否合法和有效期；
    public static function checkToken($token)
    {
        return $token;
    }

    public static function whiteUser()
    {
        // 绑定微信登陆手机号码白名单，以下顾问手机不检查微信open_id
        return [
            // 王梦思
            '17322350852',
        ];
    }

    // 检测字符串是否含有表情，含有则不合法，返回false
    public static function checkEmoji($str)
    {
        $mbLen = mb_strlen($str);
        $strArr = [];
        for ($i = 0; $i < $mbLen; $i++) {
            $strArr[] = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($strArr[$i]) >= 4) {
                return false;
            }
        }
        return true;
    }
}
