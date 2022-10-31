<?php

use yii\bootstrap\BootstrapAsset;
use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model admin\models\form\LoginForm
 */

$this->title = '登录';

BootstrapAsset::register($this);
?>
<div class="site-login">

	<h1><?= Html::encode($this->title) ?></h1>

	<?php $form = ActiveForm::begin([
		'fieldConfig' => [
			'options' => [
				'tag' => 'div',
				'class' => ['form-group'],
			],
			'template' => "{label}\n{input}\n{hint}\n{error}",
			'inputOptions' => ['class' => ['form-control']],
			'errorOptions' => [
				'tag' => 'div',
				// 这里class要用空格
				'class' => 'help-block',
			],
			'labelOptions' => ['class' => ['control-label']],
			'hintOptions' => [
				'tag' => 'div',
				'class' => ['hint-block'],
			],
		],
	]); ?>

	<?= $form->field($model, 'username')->textInput([
		'autofocus' => true,
		'maxlength' => true,
		'placeholder' => '用户名/手机号码/邮箱',
	]) ?>

	<?= $form->field($model, 'password')->passwordInput() ?>

	<?php /*= $form->field($model, 'verifyCode')->widget(Captcha::className(), [
		'captchaAction' => '/site/captcha',
		'template' => '<div class="row"><div class="col-xs-4">{input}</div><div class="col-xs-8">{image}</div></div>'
	]) */?>

	<div class="form-group">
		<button type="submit" class="btn btn-primary btn-block">登录</button>
	</div>

	<?php ActiveForm::end(); ?>
    <a target="_blank" style="display: block; text-align: center" href="https://beian.miit.gov.cn">备案号:粤ICP备2020133048号</a>
</div>
