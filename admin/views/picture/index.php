<?php

use common\helpers\Helper;
use common\helpers\Render;
use common\models\table\Picture;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel admin\models\search\PictureSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '列表';
$this->params['breadcrumbs'][] = $this->title;

$gridColumns = [
    'id',
    'date',
    //'name',
    /*[
        'attribute' => 'picture',
        'format' => 'raw',
        'value' => function ($model) {
            return Html::img(Helper::getImageUrl($model->picture), ['width' => '5px']);
        }
    ],*/
    /*[
        'header' => '上传多图',
        'format' => 'raw',
        'value' => function ($model) {
            return Html::a('上传多图', ['picture', 'id' => $model->id], ['data-pjax' => 0, 'class' => "btn btn-xs btn-primary"]);
        }
    ],*/
    [
        'attribute' => 'is_push',
        'value' => function ($model) {
            return Picture::pushMap($model->is_push);
        }
    ],
    'create_time',
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{update} {delete}'
    ],
];

?>
<div class="station-index">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('上传单张', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('上传多张', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= Render::gridView([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns
    ]); ?>
</div>
