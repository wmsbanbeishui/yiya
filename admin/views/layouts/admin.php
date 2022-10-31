<?php

use admin\assets\AppAsset;
use common\helpers\JsBlock;
use common\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/**
 * @var $this \yii\web\View
 * @var $content string
 */

AppAsset::register($this);
?>
<?php JsBlock::begin() ?>
	<script>
		$("button[type=reset]").click(function () {
			var url = "<?= Url::to([Yii::$app->controller->action->id]) ?>";
			// $.pjax.reload("#kartik-grid-pjax", {url: url});
			window.location.href = url;
		});
	</script>
<?php JsBlock::end() ?>
<?php $this->beginPage() ?>
	<!DOCTYPE html>
	<html lang="<?= Yii::$app->language ?>">
		<head>
			<meta charset="<?= Yii::$app->charset ?>">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<?= Html::csrfMetaTags() ?>
			<title><?= Html::encode($this->title) ?></title>
			<?php $this->head() ?>
		</head>
		<body>
			<?php $this->beginBody() ?>
			<div class="wrap">
				<div class="full_container" style="padding: 15px;">
					<?= Breadcrumbs::widget([
						'homeLink' => [
							'label' => '首页',
						],
						'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
					]) ?>
					<?= Alert::widget() ?>
					<?= $content ?>
				</div>
			</div>
			<?php $this->endBody() ?>
		</body>
	</html>
<?php $this->endPage() ?>
