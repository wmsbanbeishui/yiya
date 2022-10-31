<?php
if(!Yii::$app->user->isGuest){
	echo "Hello , ".Yii::$app->user->identity->name;
}
?>
