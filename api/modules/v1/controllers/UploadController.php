<?php

namespace api\modules\v1\controllers;

use common\helpers\FileHelper;
use common\helpers\Helper;
use api\modules\v1\controllers\base\BaseController;
use Yii;

class UploadController extends BaseController
{
    /**
     * 上传文件
     * @return array
     */
    public function actionFileUpload()
    {
        $key = '';
        $request = Yii::$app->request;
        $name = $request->post('name');

        if (empty($name)) {
            return [
                'code' => 101,
                'msg' => '参数不全'
            ];
        }

        if (isset($_FILES['file']['name'])) {
            $path = 'image/' . $name . '/' . date('Ymd');
            $upload = FileHelper::picUpload($_FILES['file'], $path, 1024 * 10240);

            if ($upload['errno'] == 0) {
                $key = $upload['key'];
            } else {
                return [
                    'code' => 102,
                    'msg' => $upload['msg']
                ];
            }
        } else {
            return [
                'code' => 103,
                'msg' => '请上传图片'
            ];
        }

        if (empty($key)) {
            return [
                'code' => 104,
                'msg' => '请上传文件'
            ];
        }

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'url' => $key,
                'full_url' => Helper::getImageUrl($key)
            ]
        ];
    }
}