<?php

namespace common\validator;

use yii\validators\RegularExpressionValidator;

class BankCardValidator extends RegularExpressionValidator
{
    public $pattern = "/^(\d{16}|\d{19}|\d{17})$/";
    public $message = '银行卡卡号无效';
}