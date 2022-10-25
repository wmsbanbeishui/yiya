<?php

namespace api\modules\v1\controllers;

use common\helpers\Helper;
use common\models\table\Order;
use common\services\WxService;
use common\models\table\OilCard;
use common\models\table\Customer;
use common\models\table\CustOrder;
use common\models\table\CustCoupon;
use common\models\table\CustFinance;
use common\models\table\PointsDetail;
use common\models\table\CustRecharge;
use common\models\table\OilCardFinance;
use common\models\table\OilCardRecharge;
use api\modules\v1\controllers\base\BaseController;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * 微信接口  控制器
 */
class WxController extends BaseController
{
    protected static function authAction()
    {
        return [];
    }

    protected static function normalAction()
    {
        return ['login', 'get-mobile', 'notify', 'query-pay', 'get-xcx-code', 'qrcode'];
    }

    /**
     * 微信登录
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public function actionLogin()
    {
        /*if (!Helper::is_wx_mini_program()) {
            return [
                'code' => 101,
                'msg' => '非微信小程序禁止访问',
            ];
        }*/

        $request = Yii::$app->request;
        $code = $request->post('code');

        if (!$code) {
            return [
                'code' => 102,
                'msg' => 'code为空',
            ];
        } else {
            $url = 'https://api.weixin.qq.com/sns/jscode2session';
            $query_data = [
                'appid' => Helper::get_wx_cfg('app_id'),
                'secret' => Helper::get_wx_cfg('app_secret'),
                'js_code' => $code,
                'grant_type' => 'authorization_code',
            ];
            $auth_info = Helper::curl($url, $query_data);

            Helper::fLogs("\n", 'wx_auto_login.log');
            Helper::fLogs($auth_info, 'wx_auto_login.log');

            $openid = ArrayHelper::getValue($auth_info, 'openid');
            $session_key = ArrayHelper::getValue($auth_info, 'session_key');

            if (!empty($openid) && !empty($session_key)) {
                // 将session_key 写入redis
                $redis = Yii::$app->redis;
                $session3rd = Helper::randString($len = 16);
                $redis->set($session3rd, json_encode([
                    'open_id' => $openid,
                    'session_key' => $session_key,
                ]));
                $redis->expire($session3rd, 600);

                return [
                    'code' => 0,
                    'msg' => '',
                    'data' => [
                        'open_id' => $openid,
                        'session3rd' => $session3rd
                    ]
                ];
            } else {
                return [
                    'code' => 103,
                    'msg' => '登录失败'
                ];
            }
        }
    }

    /**
     * 通过解密获取手机号
     * @return array
     */
    public function actionGetMobile()
    {
        /*if (!Helper::is_wx_mini_program()) {
            return [
                'code' => 201,
                'msg' => '非微信小程序禁止访问',
            ];
        }*/

        $request = Yii::$app->request;
        $session3rd = $request->post('session3rd');
        $encryptedData = $request->post('encryptedData');
        $iv = $request->post('iv');

        $appid = Helper::get_wx_cfg('app_id');

        if (empty($encryptedData) || empty($iv) || empty($session3rd)) {
            return [
                'code' => 202,
                'msg' => '传递信息不全'
            ];
        }

        $redis = Yii::$app->redis;
        $str = $redis->get($session3rd);
        $auth = json_decode($str, true);

        // 加载解密文件
        $common_path = Yii::getAlias('@common');
        require_once $common_path . '/WXBizDataCrypt/WXBizDataCrypt.php';

        $pc = new \WXBizDataCrypt($appid, $auth['session_key']);
        $err_code = $pc->decryptData($encryptedData, $iv, $data);

        if ($err_code != 0) {
            return [
                'code' => 203,
                'msg' => '解密数据失败'
            ];
        }

        $data = json_decode($data, true);

        return [
            'code' => 0,
            'msg' => '',
            'data' => $data
        ];
    }

    /**
     * 微信支付回调
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public function actionNotify()
    {
        $xml = file_get_contents('php://input');
        Helper::fLogs([$_SERVER, $xml], 'wx_pay_notify.log');

        $common_path = Yii::getAlias('@common');
        require_once $common_path . '/WxPay/lib/WxPay.Api.php';

        // 解析微信支付通知内容
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(@simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        // 签名步骤一：按字典序排序参数
        ksort($data);
        $string = '';
        foreach ($data as $k => $v) {
            if (in_array($k, ['', 'sign']) || is_array($v)) {
                continue;
            }
            $string .= $k . '=' . $v . '&';
        }
        $string = trim($string, '&');
        // 签名步骤二：在string后加入KEY
        $string = $string . '&key=' . \WxPayConfig::KEY;
        // 签名步骤三：MD5加密
        $string = md5($string);
        // 签名步骤四：所有字符转为大写
        $sign = strtoupper($string);

        if ($sign != $data['sign']) {
            return [
                'code' => 301,
                'msg' => 'Invalid sign',
            ];
        }

        if (!($data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS')) {
            return [
                'code' => 302,
                'msg' => 'Invalid order status',
            ];
        }

        $type = 0; // 标记订单类型

        // 先找会员卡充值订单
        $order = CustRecharge::find()
            ->where(['out_trade_no' => $data['out_trade_no']])
            ->one();
        if ($order) {
            $type = 1; // 会员卡充值
            $amount = $order->amount;

        } else { // 如果不存在，则找油卡充值订单
            $order = OilCardRecharge::find()
                ->where(['out_trade_no' => $data['out_trade_no']])
                ->one();
            if ($order) {
                $type = 2; // 油卡充值
                $amount = $order->amount;

            } else { // 如果不存在，则找微信支付加油订单
                $order = CustOrder::find()
                    ->where(['out_trade_no' => $data['out_trade_no']])
                    ->one();
                if ($order) {
                    $type = 3; // 微信支付加油
                    $amount = $order->actually_price * 100;
                }
            }
        }

        if (empty($order)) {
            return [
                'code' => 303,
                'msg' => '该交易不存在'
            ];
        }

        if ($data['total_fee'] != $amount) {
            return [
                'code' => 304,
                'msg' => 'fee not match'
            ];
        }

        // 更新订单信息
        if ($order->pay_status == 0) {
            $order->pay_status = 1;
            $order->pay_time = date('Y-m-d H:i:s');
            $order->payment = $data['total_fee'];
            $order->out_trade_no = $data['out_trade_no'];
            $order->transaction_id = $data['transaction_id'];

            if (!$order->save()) {
                $error = $order->getErrors();
                $errors = [
                    'error' => $error,
                    'type' => $type,
                    'id' => $order->id,
                    'out_trade_no' => $data['out_trade_no']
                ];
                Helper::fLogs($errors, 'wx_pay_notify_error.log');
                return [
                    'code' => 305,
                    'msg' => '更新订单失败'
                ];
            }

            $cust_info = Customer::findOne($order->cust_id);
            switch ($type) {
                case 1: // 会员卡充值
                    $add_amount = $order->amount + $order->gift_amount;
                    $add_points = $add_growth = intval($order->amount / 100); // 暂时充值1元，加1积分，加1成长值

                    if ($order->type == 1) { // 更新会员表的汽油余额
                        $balance = $cust_info->gasoline_balance + $add_amount;
                        $cust_info->gasoline_balance = $balance;

                    } else {
                        $balance = $cust_info->diesel_balance + $add_amount;
                        $cust_info->diesel_balance = $balance;
                    }

                    $points = $cust_info->points + $add_points;
                    $cust_info->points = $points;
                    $cust_info->growth = $cust_info->growth + $add_growth;
                    if (!$cust_info->save()) {
                        $error = $cust_info->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '会员卡充值：更新会员余额失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_cust_recharge_error.log');
                    }

                    // 添加会员卡的资金流水
                    $model = new CustFinance();
                    $model->station_id = $order->station_id;
                    $model->cust_id = $order->cust_id;
                    $model->finance_type = $order->type;
                    $model->type = 1; // 1-收入；2-支出
                    $model->change_type = 1; // 1-会员充值；2-加油消费
                    $model->offset = $add_amount;
                    $model->balance = $balance;
                    $model->cust_recharge_id = $order->id;
                    if (!$model->save()) {
                        $error = $model->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '会员卡充值：添加会员卡资金流水失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_cust_recharge_error.log');
                    }

                    // 添加会员积分明细
                    $points_detail = new PointsDetail();
                    $points_detail->station_id = $order->station_id;
                    $points_detail->cust_id = $order->cust_id;
                    $points_detail->type = 1; // 1-收入；2-支出
                    $points_detail->change_type = 1; // 1-会员充值奖励；2-油卡充值奖励；3-加油奖励；4-积分兑换
                    $points_detail->offset = $add_points;
                    $points_detail->points = $points;
                    $points_detail->cust_recharge_id = $order->id;
                    if (!$points_detail->save()) {
                        $error = $points_detail->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '会员卡充值：添加积分流水失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_points_error.log');
                    }
                    break;

                case 2: // 油卡充值
                    $add_amount = $order->amount + $order->gift_amount;
                    $add_points = $add_growth = intval($order->amount / 100); // 暂时充值1元，加1积分，加1成长值

                    // 更新会员积分、成长值
                    $points = $cust_info->points + $add_points;
                    $cust_info->points = $points;
                    $cust_info->growth = $cust_info->growth + $add_growth;
                    $cust_info->save();
                    if (!$cust_info->save()) {
                        $error = $cust_info->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '油卡充值：更新会员积分、成长值失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_oil_recharge_error.log');
                    }

                    // 更新油卡余额
                    $card_info = OilCard::findOne($order->card_id);
                    $balance = $card_info['balance'] + $add_amount;
                    $card_info->balance = $balance;
                    if (!$card_info->save()) {
                        $error = $card_info->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '油卡充值：更新油卡余额失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_oil_recharge_error.log');
                    }

                    // 添加油卡的资金流水
                    $model = new OilCardFinance();
                    $model->oil_card_id = $order->card_id;
                    $model->cust_id = $order->cust_id;
                    $model->station_id = $order->station_id;
                    $model->type = 1; // 1-收入；2-支出
                    $model->change_type = 1; // 1-油卡充值；2-加油消费
                    $model->offset = $add_amount;
                    $model->balance = $balance;
                    $model->oil_recharge_id = $order->id;
                    if (!$model->save()) {
                        $error = $model->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '油卡充值：添加油卡资金流水失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_oil_recharge_error.log');
                    }

                    // 添加会员积分明细
                    $points_detail = new PointsDetail();
                    $points_detail->station_id = $order->station_id;
                    $points_detail->cust_id = $order->cust_id;
                    $points_detail->type = 1; // 1-收入；2-支出
                    $points_detail->change_type = 2; // 1-会员充值奖励；2-油卡充值奖励；3-加油奖励；4-积分兑换
                    $points_detail->offset = $add_points;
                    $points_detail->points = $points;
                    $points_detail->oil_recharge_id = $order->id;
                    if (!$points_detail->save()) {
                        $error = $points_detail->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '油卡充值：添加积分流水失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_points_error.log');
                    }
                    break;

                case 3: // 微信支付加油

                    // 更新对应的订单
                    Order::updateAll(['pay_status' => 1], ['id' => $order->order_id]);

                    // 更新会员优惠券
                    CustCoupon::updateAll(['status' => 1, 'cust_order_id' => $order->id], ['id' => $order->cust_coupon_id]);

                    // 更新会员积分、成长值
                    $add_points = $add_growth = intval($order->actually_price); // 暂时消费1元，加1积分，加1成长值
                    $total_points = $cust_info->points + $add_points;
                    $total_growth = $cust_info->growth + $add_growth;
                    Customer::updateAll(['points' => $total_points, 'growth' => $total_growth], ['id' => $order->cust_id]);

                    // 添加会员积分明细
                    $points_detail = new PointsDetail();
                    $points_detail->station_id = $order->station_id;
                    $points_detail->cust_id = $order->cust_id;
                    $points_detail->type = 1; // 1-收入；2-支出
                    $points_detail->change_type = 3; // 1-会员充值奖励；2-油卡充值奖励；3-加油奖励；4-积分兑换
                    $points_detail->offset = $add_points;
                    $points_detail->points = $total_points;
                    $points_detail->cust_order_id = $order->id;
                    if (!$points_detail->save()) {
                        $error = $points_detail->getErrors();
                        $errors = [
                            'error' => $error,
                            'type' => $type,
                            'id' => $order->id,
                            'remark' => '微信支付加油：添加积分流水失败'
                        ];
                        Helper::fLogs($errors, 'wx_pay_notify_points_error.log');
                    }
                    break;
            }
        }

        Helper::wxpay_notify_return(); // 响应微信支付通知
    }

    /**
     * 查询微信支付订单
     * @return array
     * @throws \WxPayException
     */
    public function actionQueryPay()
    {
        define('WXPAY_CONFIG', Yii::getAlias('@common/WxPay/lib/WxPay.Config.php'));
        require_once Yii::getAlias('@common/WxPay/lib/WxPay.Api.php');
        require_once Yii::getAlias('@common/WxPay/WxPay.NativePay.php');
        require_once Yii::getAlias('@common/WxPay/WxPay.JsApiPay.php');

        $request = Yii::$app->request;
        $out_trade_no = $request->get('out_trade_no');

        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);

        $return = \WxPayApi::orderQuery($input);

        return [
            'code' => 0,
            'data' => $return
        ];
    }

    /**
     * 获取小程序的二维码
     * @return array|bool|string
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public function actionGetXcxCode()
    {
        $request = Yii::$app->request;
        $params = $request->post();

        return WxService::getXcxCode($params);
    }

    /**
     * 生成支付二维码图片
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public function actionQrcode()
    {
        $request = Yii::$app->request;
        $data = $request->get('data');
        Helper::fLogs($data, 'qrcode.log');

        require_once Yii::getAlias('@common/WxPay/phpqrcode/phpqrcode.php');

        \QRcode::png($request->get('data'), false, $level = QR_ECLEVEL_L, $size = 5, $margin = 4);
        exit(0);
    }
}