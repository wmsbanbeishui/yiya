<?php

namespace common\models\table;

use common\helpers\FileHelper;
use common\models\base\PictureBase;
use Yii;
use yii\helpers\ArrayHelper;

class Picture extends PictureBase
{
    public $imageFiles;
    public $up_files = [];

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['imageFiles', 'safe'],
            [['picture'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxFiles' => 5],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'is_push' => '是否推荐'
        ]);
    }

    const PUSH_0 = 0;
    const PUSH_1 = 1;

    public static function pushMap($value = null)
    {
        $map = [
            self::PUSH_0 => '否',
            self::PUSH_1 => '是',
        ];
        if ($value === null) {
            return $map;
        }
        return ArrayHelper::getValue($map, $value, $value);
    }

    public function upload()
    {
        if (!$this->validate()) {
            var_dump(current($this->getFirstErrors()));
            exit;
            return false;
        }

        $path = 'picture/';

        $dir = Yii::getAlias('@upload/') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        foreach ($this->imageFiles as $file) {
            $file_name = sprintf('%s_%s.%s', date('Ymd_His'), mt_rand(100, 999), $file->extension);

            if (!$file->saveAs($dir . $file_name)) {
                $this->addError('imageFile', '文件上传失败');
                return false;
            }

            $this->up_files[] = '/' . $path . $file_name;

            $file_path = $dir . $file_name;
            $key = $path . $file_name;

            FileHelper::qnUpload($file_path, $key);
        }
        return true;
    }
}
