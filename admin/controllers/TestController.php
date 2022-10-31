<?php

namespace admin\controllers;

use admin\models\form\TestImportForm;
use admin\controllers\base\ApiController;
use common\helpers\Excel;
use common\helpers\Helper;
use common\models\table\Answer;
use common\models\table\ArticleSearch;
use common\models\table\Question;
use common\services\TestService;
use yii\helpers\FileHelper;
use Yii;

class TestController extends ApiController
{
    protected static function authAction()
    {
        return [];
    }

    protected static function normalAction()
    {
        return ['export', 'ali-pay', 'ali-pay-notify', 'ali-pay-code', 'qrcode', 'test', 'import'];
    }

    /**
     * 打印 phpinfo信息
     */
    public function actionIndex()
    {
        echo phpinfo();
        echo '333';
        //return $this->render('index');
    }

    /**
     * 测试导入
     * @return string|\yii\web\Response
     */
    public function actionImport()
    {
        $request = Yii::$app->getRequest();

        $form = new TestImportForm();

        if ($request->getIsPost()) {
            if ($form->import()) {
                return $this->redirect('index');
            }
        } else {
            return $this->render('import', ['model' => $form]);
        }
    }

    /**
     * 下载文件
     * @return \yii\console\Response|\yii\web\Response
     */
    public function actionImportTemplate()
    {
        $response = Yii::$app->getResponse();
        return $response->sendFile(Yii::getAlias('@webroot/import_template.xlsx'));
    }

    public function actionExport()
    {
        $data = [
            [
                'id' => 1,
                'name' => '王梦思'
            ],
            [
                'id' => 2,
                'name' => '陈雷'
            ]
        ];
        $title = [
            'ID',
            'name',
        ];

        Excel::exportExcel($data, $title, $file_name = '代理商提现申请');
    }

    /**
     * 支付宝PC端支付
     * @throws \Exception
     */
    public function actionAliPay()
    {
        return TestService::aliPay();
    }

    /**
     * 支付宝支付回调
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public function actionAliPayNotify()
    {
        return TestService::aliPayNotify();
    }

    /**
     * 支付宝当面扫码支付
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\log\LogRuntimeException
     */
    public function actionAliPayCode()
    {
        return TestService::aliPayCode();
    }

    /**
     * 生成支付二维码图片
     */
    public function actionQrcode()
    {
        $request = Yii::$app->request;
        $data = $request->get('data');
        Helper::fLogs($data, 'qrcode.log');

        require_once Yii::getAlias('@common/phpqrcode/phpqrcode.php');

        \QRcode::png($request->get('data'), false, $level = QR_ECLEVEL_L, $size = 5, $margin = 4);
        exit(0);
    }

    // 获取问卷调查数据列表
    public function actionGetQuestions()
    {
        return Question::find()
            ->with(['options'])
            ->asArray()
            ->all();
    }

    // 获取某个用户的问卷答题情况
    public function actionGetUserAnswer($user_id)
    {
        // 问卷调查数据列表
        $question_list = Question::find()
            ->with(['options'])
            ->asArray()
            ->all();
        //var_dump($question_list);exit;

        $answer_list = Answer::find()
            ->where(['user_id' => $user_id])
            ->asArray()
            ->all();
        //var_dump($answer_list);exit;

        foreach ($question_list as $key => &$question){

            foreach ($question['options'] as $k => &$option) {
                $option['selected'] = '0';

                foreach ($answer_list as $index =>$answer) {
                    if ($answer['question_id'] == $question['id'] && $answer['option_id'] == $option['id']) {
                        $question_list[$key]['options'][$k]['selected'] = '1';
                        unset($answer_list[$index]);
                    }
                }
            }
            unset($option);
        }
        unset($question);

        return $question_list;
    }

    // 获取问卷答题统计
    public function actionGetAnswerCount()
    {
        // 问卷调查数据列表
        $question_list = Question::find()
            ->with(['options'])
            ->asArray()
            ->all();
        //var_dump($question_list);exit;

        $answer_list = Answer::find()
            ->select(['question_id', 'option_id', 'COUNT(id) as num'])
            ->groupBy(['option_id'])
            ->asArray()
            ->all();
        //var_dump($answer_list);exit;

        foreach ($question_list as $key => &$question){

            foreach ($question['options'] as $k => &$option) {
                $option['count'] = '0';

                foreach ($answer_list as $index =>$answer) {
                    if ($answer['question_id'] == $question['id'] && $answer['option_id'] == $option['id']) {
                        $question_list[$key]['options'][$k]['count'] = $answer['num'];
                        unset($answer_list[$index]);
                    }
                }
            }
            unset($option);
        }
        unset($question);

        return $question_list;
    }

    // 问卷调查及答题列表
    public function actionAllAnswer()
    {
        $user_id = 1;

        // 问卷调查数据列表
        $question_list = Question::find()
            ->with(['options'])
            ->asArray()
            ->all();
        //var_dump($question_list);exit;

        $answer_list = Answer::find()
            ->select(['question_id', 'option_id', 'COUNT(id) as num'])
            ->groupBy(['option_id'])
            ->asArray()
            ->all();
        //var_dump($answer_list);exit;

        foreach ($question_list as $key => &$question){

            foreach ($question['options'] as $k => &$option) {
                $option['count'] = '0';

                foreach ($answer_list as $index =>$answer) {
                    if ($answer['question_id'] == $question['id'] && $answer['option_id'] == $option['id']) {
                        $question_list[$key]['options'][$k]['count'] = $answer['num'];
                        unset($answer_list[$index]);
                    }
                }
            }
            unset($option);
        }
        unset($question);

        var_dump($question_list);



        exit;
    }

    public function actionTest()
    {
        return $this->render('index');
    }
}
