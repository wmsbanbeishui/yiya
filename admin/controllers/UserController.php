<?php

namespace admin\controllers;

use admin\controllers\base\BaseController;
use admin\models\form\LoginForm;
use common\helpers\Helper;
use common\helpers\Message;
use common\models\table\AdminLog;
use Yii;
use yii\filters\AccessControl;

class UserController extends BaseController
{
	/**
	 * @inheritdoc
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
							'login',
						],
						'roles' => ['?'],
					],
					[
						'allow' => true,
						'actions' => [
							'logout',
						],
						'roles' => ['@'],
					],
				],
				'denyCallback' => function ($rule, $action) {
					return $this->goBack();
				},
			],
		];
	}

	public function actionLogin()
	{
		if (!Yii::$app->user->isGuest) {
			return $this->goHome();
		}

		$model = new LoginForm();
		if ($model->load(Yii::$app->request->post())) {
			if ($model->login()) {
				return $this->redirect(['/']);
			} else {
				Message::setErrorMsg('登录失败');
			}
		}

		return $this->render('login', [
			'model' => $model,
		]);

	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		Message::setMessage('已登出');
		return $this->redirect(['login']);
	}
}
