<?php

use common\helpers\Helper;
use common\helpers\JsBlock;
use common\models\table\Picture;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\table\Picture */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="picture-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'class' => ['form-horizontal'],
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => ['control-label', 'col-lg-1']],
        ],
    ]); ?>

    <?php /*= $form->field($model, 'name')->textInput(['maxlength' => true]) */?>

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

    <div class="row form-group">
        <label class="col-lg-1 text-right" style="padding:10px 15px">图片</label>
        <div class="col-lg-3">
            <img id="pic2" src="<?= Helper::getImageUrl($model->picture) ?: '/static/admin/images/upload.jpg' ?>"
                 title="点击选择图片" width="<?= Helper::getImageUrl($model->picture) ? 400 : 100 ?>" class="img-responsive"/>
        </div>
        <div class="col-lg-">
            <input id="upload2" name="picture" accept="image/*" type="file" style="display: none"/>
        </div>
    </div>

    <?= $form->field($model, 'is_push')->dropDownList(Picture::pushMap()) ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php JsBlock::begin() ?>
<script type="text/javascript">
    $(function () {
        $("#pic2").click(function () {
            $("#upload2").click();
        });
        $("#upload2").on("change", function () {
            var objUrl = getObjectURL(this.files[0]);
            if (objUrl) {
                $("#pic2").attr("src", objUrl); //将图片路径存入src中，显示出图片
            }
        });
    })

    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }
</script>
<?php JsBlock::end() ?>
