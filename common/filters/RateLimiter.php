<?php

namespace common\filters;

use Yii;
use yii\base\ActionFilter;
use yii\base\InvalidValueException;
use yii\web\Request;
use yii\web\TooManyRequestsHttpException;

class RateLimiter extends ActionFilter
{
    /**
     * @var string the message to be displayed when rate limit exceeds
     */
    public $errorMessage = 'Rate limit exceeded.';
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;

    private $_model;


    public function setModel($className)
    {
        $instance = Yii::createObject($className);
        if ($instance instanceof RateLimitInterface) {
            $this->_model = $instance;
        } else {
            throw new InvalidValueException('The model object must implement RateLimitInterface.');
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->request === null) {
            $this->request = Yii::$app->getRequest();
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->checkRateLimit($this->_model, $this->request, $action);
        return true;
    }

    /**
     * Checks whether the rate limit exceeds.
     * @param RateLimitInterface $model the current user
     * @param Request $request
     * @param \yii\base\Action $action the action to be executed
     * @throws TooManyRequestsHttpException if rate limit exceeds
     */
    public function checkRateLimit($model, $request, $action)
    {
        $current = time();

        list ($limit, $window) = $model->getRateLimit($request, $action);
        list ($allowance, $timestamp) = $model->loadAllowance($request, $action);

        $allowance += (int)(($current - $timestamp) * $limit / $window);
        if ($allowance > $limit) {
            $allowance = $limit;
        }

        if ($allowance < 1) {
            $allowance = 0;
            $model->saveAllowance($request, $action, $allowance, $current);
            $model->saveLog($request, $action, $allowance, $current);
            throw new TooManyRequestsHttpException($this->errorMessage);
        } else {
            $allowance--;
            $model->saveAllowance($request, $action, $allowance, $current);
            $model->saveLog($request, $action, $allowance, $current);
        }
    }
}
