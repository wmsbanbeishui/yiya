<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\table\Picture */

$this->title = '添加';
$this->params['breadcrumbs'][] = ['label' => '列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="picture-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
