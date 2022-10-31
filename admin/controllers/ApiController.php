<?php

namespace admin\controllers;

use admin\controllers\base\AuthController;
use common\helpers\Helper;
use common\models\table\ArticleSearch;
use common\models\table\Category;
use common\models\table\CodeMsg;
use Yii;

class ApiController extends AuthController
{
    public function actionLevel()
    {
        Yii::$app->response->format = 'json';
        $level_id = Yii::$app->request->post('level_id');
        if ($level_id == null) {
            return ['code' => 0, 'data' => []];
        }
        $data = Category::find()
            ->select(['id', 'name'])
            ->where(['level' => $level_id])
            ->orderBy(['order_index' => SORT_DESC])
            ->all();
        return ['code' => 0, 'data' => $data];
    }

    public function actionSearch()
    {
        Yii::$app->response->format = 'json';
        $request = Yii::$app->request;
        $keyword = $request->get('keyword');

        //利用elasticsearch进行检索
        $searchModel = ArticleSearch::find()->query([
            'multi_match' => [
                'query' => $keyword,
                'fields' => ['title', 'description']
            ]
        ]);

        $data = $searchModel->highlight([
            "pre_tags" => ['<span class="hightlight">'],
            "post_tags" => ["</span>"],
            "fields" => [
                "title" => new \stdClass(),
                "description" => new \stdClass(),
            ]
        ])->asArray()->all();

       // var_dump($data);exit;


        return ['code' => 0, 'data' => $data];
    }

    public function actionAliPay()
    {
        Yii::$app->response->format = 'json';
        require_once Yii::getAlias('@common/alipay/aop/request/AlipayTradePagePayRequest.php');
        require_once Yii::getAlias('@common/alipay/aop/AopClient.php');
        $config = Helper::getParam('alipay');

        Helper::fLogs($config, 'alipay.log');

        $aop = new \AopClient();
        $aop->gatewayUrl = $config['gatewayUrl'];
        $aop->appId = $config['app_id'];
        $aop->rsaPrivateKey = $config['merchant_private_key'];
        $aop->alipayrsaPublicKey = $config['alipay_public_key'];
        $aop->signType = $config['sign_type'];

        $request = new \AlipayTradePagePayRequest();
        $request->setNotifyUrl($config['notify_url']);
        $request->setReturnUrl($config['return_url']);

        $pay_data = [
            'out_trade_no' => Helper::gen_order_no(),
            'product_code' => $config['product_code'],
            'total_amount' => '0.1',
            'subject' => '商品1',
            'body' => '特蓝图'
        ];

        $pay_data = json_encode($pay_data);
        $request->setBizContent($pay_data);
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return [
                'code' => 0,
                'msg' => '操作成功'
            ];
        } else {
            return [
                'code' => 101,
                'msg' => '操作失败'
            ];
        }
    }

    public function actionAliPayNotify()
    {
        $data = Yii::$app->request->post();
        $config = Helper::getParam('alipay');
        $service_obj = new \AlipayTradeService($config);
        $result = $service_obj->check($data);

        if ($result) {
            if ($data['trade_status'] === 'TRADE_SUCCESS') {
                $model = new CodeMsg();
                $model->mobile = '17322350852';
                $model->code = '2222';
                $model->type = 3;
                $model->deadline = time() + 120;
                $model->save();

                echo 'success';
            }
        } else {
            echo '非法操作';
        }

    }
}
