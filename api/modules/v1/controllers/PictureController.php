<?php

namespace api\modules\v1\controllers;

use api\modules\v1\controllers\base\BaseController;
use common\services\PictureService;
use Yii;

class PictureController extends BaseController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $pageSize = $request->get('pageSize');
        $page = $request->get('page');

        return PictureService::getList($name = null, $pageSize, $page);
    }
}