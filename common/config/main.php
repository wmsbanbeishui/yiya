<?php

$components = array_merge(
	require(__DIR__ . '/components.php'),
	require(__DIR__ . '/db.php')// 数据库
);

$db_local = __DIR__ . '/db-local.php';
if (file_exists($db_local)) {
	$components = array_merge(
		$components,
		require($db_local)
	);
}

$config = [
    'id' => 'yiya',
    'name' => '一丫相册',
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'components' => $components,
	'language' => 'zh-CN',
	'timeZone' => 'Asia/Shanghai',
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
	],
];

return $config;
