<?php

namespace api\modules\v1\controllers;

use api\modules\v1\controllers\base\BaseController;
use common\helpers\Helper;
use common\services\PictureService;
use Yii;

class PictureController extends BaseController
{
    protected static function normalAction()
    {
        return ['index', 'index2'];
    }

    public function actionIndex()
    {
        $request = Yii::$app->request;
        $param = $request->get('param');

        $param = json_decode($param, true);

        //var_dump($param);exit;

        $name = $param['name'] ?: null;
        $pageSize = $param['pageSize'];
        $page = $param['page'];

        return PictureService::getList($name, $pageSize, $page);
    }

    public function actionIndex2()
    {
        $request = Yii::$app->request;
        $pageSize = $request->get('pageSize');
        $page = $request->get('page');

        return PictureService::getList($name = null, $pageSize, $page);
    }
}