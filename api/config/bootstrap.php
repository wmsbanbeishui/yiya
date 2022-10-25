<?php

Yii::$container->set('yii\data\Pagination', [
    'validatePage' => false,
    'pageSizeLimit' => [1, 999],
]);
