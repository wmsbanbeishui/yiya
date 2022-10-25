<?php

$config = [
    'formatter' => [
        'nullDisplay' => '-',
        'dateFormat' => 'php:Y-m-d',
        'datetimeFormat' => 'php:Y-m-d H:i:s',
    ],
    'cache' => [
        'class' => 'yii\redis\Cache',
        'redis' => 'redis',
        'keyPrefix' => 'yii_',
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'common\helpers\FileTarget',
                'levels' => ['error', 'warning'],
            ],
        ],
    ],
];

return $config;
