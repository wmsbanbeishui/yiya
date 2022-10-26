<?php

namespace api\modules\v1\controllers;

use common\helpers\FileHelper;
use common\helpers\Helper;
use api\modules\v1\controllers\base\BaseController;
use common\services\IndexService;
use Yii;

class IndexController extends BaseController
{
    protected static function normalAction()
    {
        return ['index'];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $pageSize = $request->get('pageSize');
        $page = $request->get('page');

        return IndexService::getIndex($pageSize, $page);
    }
}