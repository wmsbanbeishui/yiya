<?php

use common\models\table\Picture;
use common\widgets\DateRangePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model admin\models\search\PictureSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="picture-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="pl form-inline">

        <?= $form->field($model, 'name')->textInput(['style' => 'width: 200px; margin-right: 20px']) ?>

        <?= $form->field($model, 'is_push')->dropDownList(Picture::pushMap(), ['prompt' => '全部', 'id' => 'push', 'style' => 'width: 100px; margin-right: 20px']) ?>

        <?= $form->field($model, 'date')->widget(DateRangePicker::className()) ?>

        <div class="form-group" style="margin-left: 20px">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
            <div class="help-block"></div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>

</div>
