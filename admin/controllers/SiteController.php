<?php
namespace admin\controllers;

use Yii;
use yii\web\Controller;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'actions' => [
							'index',
						],
						'roles' => ['@'],
					],
					[
						'allow' => true,
						'actions' => [
							'error',
							'captcha',
						],
					],
				],
			],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
			'captcha' => [
				'class' => CaptchaAction::className(),
				'height' => 30,
				'width' => 120,
				'padding' => 1,
				'offset' => 6,
				'minLength' => 4,
				'maxLength' => 4,
			],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
