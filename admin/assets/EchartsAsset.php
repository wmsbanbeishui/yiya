<?php

namespace admin\assets;

use yii\web\AssetBundle;

/**
 * Main admin application asset bundle.
 */
class EchartsAsset extends AssetBundle
{
	public $sourcePath = '@bower/echarts/dist';
	public $js = [
		'echarts.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
	];
}
