<?php

namespace common\validator;

use yii\validators\RangeValidator;

/**
 * 访客来源验证
 */
class FromSourceValidator extends RangeValidator
{
    const FROM_SOURCE_WX = 1;// 微信公众号
    const FROM_SOURCE_ANDROID = 2;// 安卓app
    const FROM_SOURCE_IOS = 3;// ios app
    const FROM_SOURCE_PC = 4;// pc网页
    const FROM_SOURCE_USER_ADD = 5;// 顾问添加
    const FROM_SOURCE_WX_MINI_PROGRAM = 6;// 微信小程序

    public $range = [
        self::FROM_SOURCE_WX,
        self::FROM_SOURCE_ANDROID,
        self::FROM_SOURCE_IOS,
        self::FROM_SOURCE_PC,
        self::FROM_SOURCE_USER_ADD,
        self::FROM_SOURCE_WX_MINI_PROGRAM,
    ];
}
