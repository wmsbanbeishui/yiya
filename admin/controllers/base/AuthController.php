<?php

namespace admin\controllers\base;

use common\helpers\Helper;
use yii\web\Controller;
use Yii;

class AuthController extends BaseController
{
	/**
	 * {@inheritdoc}
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * 检查权限
	 * @author luotaipeng
	 */
	public function beforeAction($action)
	{
		if (!parent::beforeAction($action)) {
			return false;
		}

		if (Yii::$app->user->id == 1) {
			return true;
		}

		// T-1284 登陆已超时
		if (!Yii::$app->user->id) {
			if (Yii::$app->request->isAjax) {
				$error = [
					'errno' => 401,
					'errmsg' => '登陆已超时',
				];
				$error['code'] = $error['errno'];
				$error['msg'] = $error['errmsg'];
				Helper::json_output($error);
			}
			echo '<h1 style="color: #f00; text-align: center; margin-top: 25%;">登陆已超时<h1>';
			echo '<script>setTimeout(function(){ window.top.location.reload(); }, 5000)</script>';
			exit(0);
		}

		$request_uri = '/' . Yii::$app->controller->route;
		// T-1284 无操作权限
		if (!Yii::$app->user->can($request_uri) && Yii::$app->getErrorHandler()->exception === null) {
			if (Yii::$app->request->isAjax) {
				$error = [
					'errno' => 401,
					'errmsg' => '无操作权限',
				];
				$error['code'] = $error['errno'];
				$error['msg'] = $error['errmsg'];
				Helper::json_output($error);
			}
			echo '<h1 style="color: #f00; text-align: center; margin-top: 25%;">无操作权限<h1>';
			echo '<script>setTimeout(function(){ window.top.location.reload(); }, 5000)</script>';
			exit(0);
		}

		return true;
	}
}
