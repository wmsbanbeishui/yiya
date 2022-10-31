<?php

use common\models\table\Picture;
use kartik\date\DatePicker;
use admin\assets\AppAsset;
use kartik\file\FileInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */

$this->title = '上传多图';
$this->params['breadcrumbs'][] = ['label' => '列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

AppAsset::register($this);
$this->registerCssFile('/static/admin/css/index.css?');

?>

<div class="product-binding" style="margin-left: 40px">
    <div class="product-binding-form">
        <?php $form = ActiveForm::begin([
            'options' => [
                'class' => ['form-horizontal'],
                'enctype' => 'multipart/form-data',
            ],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-lg-6\">{input}</div>\n<div class=\"col-lg-5\">{error}</div><div class='col-lg-offset-1 col-lg-11'>{hint}</div>",
                'labelOptions' => ['class' => ['control-label', 'col-lg-1']],
            ],
        ]); ?>

        <?php
        $pluginOptions = [
            'showUpload' => false,
            'dropZoneEnabled' => false,
            'initialPreviewAsData' => true,
            'initialPreviewShowDelete' => false,
            'previewFileType' => 'image',
            'allowedPreviewTypes' => ['image'],
            'allowedFileExtensions' => ['jpg', 'png'],
            'showRemove' => true,
            'fileActionSettings' => [
                // 设置具体图片的查看属性为false,默认为true
                'showZoom' => false,
                // 设置具体图片的上传属性为true,默认为true
                'showUpload' => false,
                // 设置具体图片的移除属性为true,默认为true
                'showRemove' => false,
            ],
        ];

        ?>

        <?= $form->field($model, 'is_push')->dropDownList(Picture::pushMap()) ?>

        <div class="form-group">
            <label class="control-label col-lg-1">日期</label>
            <div class="col-lg-3">
                <?php echo DatePicker::widget([
                    'name' => 'Picture[date]',
                    'value' => $model->isNewRecord ? date('Y-m-d') : $model->date,
                    'options' => ['id' => 'PictureDate', 'placeholder' => ''],
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true,
                    ],
                ]); ?>
            </div>
            <div class="col-lg-8">
                <div class="help-block"></div>
            </div>
        </div>

        <?= $form->field($model, 'picture[]')->widget(FileInput::className(), [
            'resizeImages' => true,
            'options' => [
                'accept' => 'image/*',
                'multiple' => true,
            ],
            'pluginOptions' => $pluginOptions,
            'pluginEvents' => [],
        ]);
        ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('提交', ['class' => 'btn btn-success']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>


