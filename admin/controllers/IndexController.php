<?php

namespace admin\controllers;

use admin\controllers\base\AuthController;
use yii\filters\VerbFilter;

/**
 * PictureController implements the CRUD actions for Picture model.
 */
class IndexController extends AuthController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Picture models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}