<?php

namespace common\helpers;

use Yii;

class FileHelper
{
    /**
     * 上传图片
     * @param $file
     * @param string $path
     * @param int $limit_size
     * @return array
     */
    public static function picUpload($file, $path = 'upload', $limit_size = 1024000)
    {
        $allowedExts = ["gif", "jpeg", "jpg", "png"];
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));

        $type = ["image/gif", "image/jpeg", "image/jpg", "image/pjpeg", "image/x-png", "image/png"];
        $f_type = $file["type"];

        if (!in_array($extension, $allowedExts)) {
            return ['errno' => 101, 'msg' => 'not image extension'];
        }
        if (!in_array($f_type, $type)) {
            return ['errno' => 102, 'msg' => 'not image type'];
        }
        if ($file['size'] > $limit_size) {
            return ['errno' => 103, 'msg' => 'file size too large'];
        }
        if ($file["error"] > 0) {
            return ['errno' => 103, 'msg' => $file["error"]];
        }

        $file_name = sprintf('%s_%s.%s', date('Ymd_His'), mt_rand(100, 999), $extension);
        $dir = Yii::getAlias('@upload/') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $file_path = $dir . '/' . $file_name;
        if (move_uploaded_file($file["tmp_name"], $file_path)) {
            return ['errno' => 0, 'file_path' => $file_path, 'key' => '/' . $path . '/' . $file_name, 'file_name' => $file_name];
        } else {
            return ['errno' => 500, 'msg' => 'upload erron'];
        }
    }

    /**
     * 上传xml文件
     * @param $file
     * @param string $path
     * @param int $limit_size
     * @return array
     */
    public static function xmlUpload($file, $path = 'upload', $limit_size = 1024000)
    {
        $allowedExts = ["xml"];
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));

        $type = ["text/xml"];
        $f_type = $file["type"];

        if (!in_array($extension, $allowedExts)) {
            return ['errno' => 101, 'msg' => 'not xml extension'];
        }
        if (!in_array($f_type, $type)) {
            return ['errno' => 102, 'msg' => 'not xml type'];
        }
        if ($file['size'] > $limit_size) {
            return ['errno' => 103, 'msg' => 'file size too large'];
        }
        if ($file["error"] > 0) {
            return ['errno' => 103, 'msg' => $file["error"]];
        }

        $file_name = sprintf('%s_%s.%s', date('Ymd_His'), mt_rand(100, 999), $extension);
        $dir = Yii::getAlias('@upload/') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);

        }

        $file_path = $dir . '/' . $file_name;
        if (move_uploaded_file($file["tmp_name"], $file_path)) {
            return ['errno' => 0, 'file_path' => $file_path, 'key' => '/' . $path . '/' . $file_name, 'file_name' => $file_name];
        } else {
            return ['errno' => 500, 'msg' => 'upload erron'];
        }
    }

    /**
     * 上传zip文件
     * @param $file
     * @param string $path
     * @param int $limit_size
     * @return array
     */
    public static function zipUpload($file, $path = 'upload', $limit_size = 1024000)
    {
        $allowedExts = ["zip"];
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));

        $type = ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'];
        $f_type = $file["type"];

        if (!in_array($extension, $allowedExts)) {
            return ['errno' => 101, 'msg' => 'not zip extension'];
        }
        if (!in_array($f_type, $type)) {
            return ['errno' => 102, 'msg' => 'not zip type'];
        }
        if ($file['size'] > $limit_size) {
            return ['errno' => 103, 'msg' => 'file size too large'];
        }
        if ($file["error"] > 0) {
            return ['errno' => 103, 'msg' => $file["error"]];
        }

        $file_name = sprintf('%s_%s.%s', date('Ymd_His'), mt_rand(100, 999), $extension);
        $dir = Yii::getAlias('@upload/') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $file_path = $dir . '/' . $file_name;
        if (move_uploaded_file($file["tmp_name"], $file_path)) {
            return ['errno' => 0, 'file_path' => $file_path, 'key' => '/' . $path . '/' . $file_name, 'file_name' => $file_name];
        } else {
            return ['errno' => 500, 'msg' => 'upload erron'];
        }
    }

    /**
     * 上传视频文件
     * @param $file
     * @param string $path
     * @param int $limit_size
     * @return array
     */
    public static function videoUpload($file, $path = 'upload', $limit_size = 1024000)
    {
        $allowedExts = ['mp4', 'mov'];
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));

        $type = ["video/mp4", 'video/quicktime'];
        $f_type = $file["type"];

        if (!in_array($extension, $allowedExts)) {
            return ['errno' => 101, 'msg' => 'not mp4 or mov extension'];
        }
        if (!in_array($f_type, $type)) {
            return ['errno' => 102, 'msg' => 'not mp4 or mov type'];
        }
        if ($file['size'] > $limit_size) {
            return ['errno' => 103, 'msg' => 'file size too large'];
        }
        if ($file["error"] > 0) {
            return ['errno' => 103, 'msg' => $file["error"]];
        }

        $file_name = sprintf('%s_%s.%s', date('Ymd_His'), mt_rand(100, 999), $extension);
        $dir = Yii::getAlias('@upload/') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $file_path = $dir . '/' . $file_name;
        if (move_uploaded_file($file["tmp_name"], $file_path)) {
            return ['errno' => 0, 'file_path' => $file_path, 'key' => '/' . $path . '/' . $file_name, 'file_name' => $file_name];
        } else {
            return ['errno' => 500, 'msg' => 'upload erron'];
        }
    }

    /**
     * 上传音频文件
     * @param $file
     * @param string $path
     * @param int $limit_size
     * @return array
     */
    public static function audioUpload($file, $path = 'upload', $limit_size = 1024000)
    {
        $allowedExts = ['wav', 'mp3'];
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));

        $type = ['audio/wave', 'audio/mp3', 'audio/wav', 'audio/mpeg'];
        $f_type = $file["type"];

        if (!in_array($extension, $allowedExts)) {
            return ['errno' => 101, 'msg' => 'not mp3 extension'];
        }
        if (!in_array($f_type, $type)) {
            return ['errno' => 102, 'msg' => 'not mp3 type'];
        }
        if ($file['size'] > $limit_size) {
            return ['errno' => 103, 'msg' => 'file size too large'];
        }
        if ($file["error"] > 0) {
            return ['errno' => 103, 'msg' => $file["error"]];
        }

        $file_name = sprintf('%s_%s.%s', date('Ymd_His'), mt_rand(100, 999), $extension);
        $dir = Yii::getAlias('@upload/') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $file_path = $dir . '/' . $file_name;
        if (move_uploaded_file($file["tmp_name"], $file_path)) {
            return ['errno' => 0, 'file_path' => $file_path, 'key' => '/' . $path . '/' . $file_name, 'file_name' => $file_name];
        } else {
            return ['errno' => 500, 'msg' => 'upload erron'];
        }
    }

    /**
     * 文件上传，不限制文件类型
     * @param $file
     * @param string $path
     * @param string $temp_name
     * @param int $limit_size
     * @return array
     */
    public static function fileUpload($file, $path = 'upload', $temp_name = 'tempName', $limit_size = 1024000)
    {
        if ($file['size'] > $limit_size) {
            return ['errno' => 103, 'msg' => 'file size too large'];
        }

        $file_name = $file['name'];
        $dir = Yii::getAlias('@upload/') . $path;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $file_path = $dir . '/' . $file_name;
        if (move_uploaded_file($file[$temp_name], $file_path)) {
            return ['errno' => 0, 'file_path' => $file_path, 'key' => '/' . $path . '/' . $file_name, 'file_name' => $file_name];
        } else {
            return ['errno' => 500, 'msg' => 'upload erron'];
        }
    }

    /**
     * 解压文件
     * @param $file_name
     * @param string $path
     * @return array
     */
    public static function unzip($file_name, $path = 'upload')
    {
        // 解压文件
        if (!file_exists($path)) {
            mkdir($path, 0777, true);

            $zip = new \ZipArchive();
            if ($zip->open($file_name) === true) {
                $docnum = $zip->numFiles;
                for ($i = 0; $i < $docnum; $i++) {
                    $statInfo = $zip->statIndex($i, \ZipArchive::FL_ENC_RAW);
                    $filename = self::transcoding($statInfo['name']);
                    if ($statInfo['crc'] == 0) {
                        //新建目录
                        if (!is_dir($path . '/' . substr($filename, 0, -1))) mkdir($path . '/' . substr($filename, 0, -1), 0775, true);
                    } else {
                        //拷贝文件
                        copy('zip://' . $file_name . '#' . $zip->getNameIndex($i), $path . '/' . $filename);
                    }
                }
                $zip->close();

                return [
                    'errno' => 0,
                    'msg' => '解压成功'
                ];
            } else {
                return [
                    'errno' => 500,
                    'msg' => '解压文件失败'
                ];
            }
        } else {
            return [
                'errno' => 0,
                'msg' => '解压过了'
            ];
        }
    }

    public static function transcoding($fileName)
    {
        $encoding = mb_detect_encoding($fileName, ['UTF-8', 'GBK', 'BIG5', 'CP936']);
        if (DIRECTORY_SEPARATOR == '/') {    //linux
            $filename = iconv($encoding, 'UTF-8', $fileName);
        } else {  //win
            $filename = iconv($encoding, 'GBK', $fileName);
        }
        return $filename;
    }

}
