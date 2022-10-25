<?php

use yii\web\Response;

$config = [
    'cache' => [
        'class' => 'yii\redis\Cache',
        'keyPrefix' => 'yii_',
    ],
    'request' => [
        'enableCsrfValidation' => false,
        'parsers' => [
            'application/json' => 'yii\web\JsonParser',
        ],
    ],
    'response' => [
        'format' => Response::FORMAT_JSON,
        'on beforeSend' => function ($event) {
            $response = $event->sender;
            if (isset($response->data['status']) && in_array($response->data['status'], [401, 404, 405])) {
                $response->data = [
                    'success' => $response->isSuccessful,
                    'code' => $response->data['status'],
                    'msg' => $response->data['name'],
                ];
            }
        },
    ],
    'user' => [
        'identityClass' => 'common\models\table\User',
        'enableSession' => false,
        //'enableAutoLogin' => true,
        'identityCookie' => ['name' => '_identity-m', 'httpOnly' => true],
        // 'loginUrl' => ['login'],
    ],
    'session' => [
        // this is the name of the session cookie used for login on the frontend
        //'class' => 'yii\web\Session',
        'name' => 'advanced-m',
    ],
    'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
    ],
];

return $config;