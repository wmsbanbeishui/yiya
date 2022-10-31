<?php

use yii\helpers\Html;
use kartik\file\FileInput;
use yii\widgets\ActiveForm;

$this->title = "测试导入";

?>

<?php $form = ActiveForm::begin([
	'options' => [
		'class' => ['form-horizontal'],
	],
	'fieldConfig' => [
		'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div><div class='col-lg-offset-1 col-lg-11'>{hint}</div>",
		'labelOptions' => ['class' => ['control-label', 'col-lg-1']],
	],
]); ?>

<?= $form->field($model, 'file')->widget(FileInput::className(), [
	'pluginOptions' => [
		'showUpload' => false,
		'showPreview' => false,
		'dropZoneEnabled' => false,
	],
	'options' => [
		'accept' => ['.xls', '.xlsx', 'cvs']
	],
])?>

<div class="form-group">
	<div class="col-lg-offset-1 col-lg-11">
	<button type="submit" class="btn btn-success">导入</button>
	<?= Html::a('下载导入模板', ['import-template'], ['class' => ['btn', 'btn-primary']])?>
</div>

<?php ActiveForm::end(); ?>
