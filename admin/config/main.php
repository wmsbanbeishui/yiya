<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$config = [
    'id' => 'admin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'admin\controllers',
    'bootstrap' => ['log'],
	'defaultRoute' => 'site',
	'params' => $params,
	'layout' => 'admin',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-admin',
        ],
		'user' => [
			'identityClass' => 'common\models\table\User',
			'enableAutoLogin' => true,
			'identityCookie' => ['name' => '_identity-admin', 'httpOnly' => true],
			'loginUrl' => '/login',
		],
        'session' => [
            // this is the name of the session cookie used for login on the admin
            'name' => 'advanced-admin',
        ],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => require __DIR__.'/route.php',
		],
		'assetManager' => [
			'bundles' => [
				'yii\web\JqueryAsset' => [
					'sourcePath' => null,
					'basePath' => '@webroot',
					'baseUrl' => '@web',
					'js' => ['static/admin/js/jquery/2.2.4/jquery.min.js'],
				],
				'yii\bootstrap\BootstrapAsset' => [
					'sourcePath' => null,
					'basePath' => '@webroot',
					'baseUrl' => '@web',
					'css' => ['static/admin/css/bootstrap.min.css'],
				],
			],
			'appendTimestamp' => true,
		],
    ],
	'modules' => [
		'gridview' => [
			'class' => 'kartik\grid\Module',
		],
	],
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	/*$config['bootstrap'][] = 'debug';
	$config['modules']['debug'] = [
		'class' => 'yii\debug\Module',
		'allowedIPs' => ['*.*.*.*'],
	];*/
}

return $config;
