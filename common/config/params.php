<?php

$config = [
    // 微信app_id
    'wx_app_id' => 'wx5efce0164d648797',
    // 微信app_secret
    'wx_app_secret' => 'db6a0c1f2923afc7d1a733cbb943e611',

    // 验证码等待秒数
    'sms_code_wait_second' => 60,

    // 每日短信总量
    'sms_daily_all_limit' => 5000,

    // 单个手机号短信总量
    'sms_single_limit' => 1000,

    // 每日单个手机号短信总量
    'sms_daily_single_limit' => 100,

    // 检查支付结果频率(每/秒), 默认每2秒检查一次
    'check_pay_rate' => 2,

    // 支付超时时间(秒), 默认为10分钟
    'pay_timeout' => 600,
];

return $config;
