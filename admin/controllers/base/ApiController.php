<?php

namespace admin\controllers\base;

use common\helpers\Helper;
use yii\web\Response;
use yii\rest\Controller;
use yii\filters\ContentNegotiator;
use yii\filters\auth\QueryParamAuth;

/**
 * API基础控制器
 */
class ApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Helper::set_cors();
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ]
            ],
            'authenticator' => [
                'class' => QueryParamAuth::className(),
                // 如果当前action不存在以下两个数组内，都会进行权限检查
                'only' => static::authAction(),
                'except' => static::normalAction(),
            ]
        ]);
    }

    /**
     * 需要登陆的路由
     * @return array
     */
    protected static function authAction()
    {
        return [];
    }

    /**
     * 不需要登陆的路由
     * @return array
     */
    protected static function normalAction()
    {
        return [];
    }
}