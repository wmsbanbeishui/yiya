<?php

namespace common\services;

use common\helpers\Helper;
use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class QiniuService
{
    /**
     * 获取七牛图片列表访问地址
     * @param $img_list
     * @param bool $private
     * @param null $bucket
     * @param int $expires
     * @return array|mixed|string|string[]
     */
    public static function get_img_url($img_list, $private = false, $bucket = null, $expires = 3600) {
        if (empty($img_list)) {
            return is_array($img_list) ? [] : '';
        }
        if (is_string($img_list)) {
            $decode_list = json_decode($img_list, true);
            if (is_null($decode_list)) {
                $img_list = [$img_list];
                $single = true;
            } else {
                $img_list = $decode_list;
            }
        }
        if (empty($img_list) || !is_array($img_list)) {
            return [];
        }

        $map = [
            'share' => Helper::getParam('qiniu_share_host'),
        ];

        $private_bucket = Helper::getParam('qiniu_priv_bucket');
        $public_bucket = Helper::getParam('qiniu_pub_bucket');
        if (!isset($map[$bucket])) {
            $bucket = $private ? $private_bucket : $public_bucket;
            $map[$bucket] = $private ? Helper::getParam('qiniu_priv_host') : Helper::getParam('qiniu_pub_host');
        }

        if (empty($bucket)) {
            $bucket = $private ? $private_bucket : $public_bucket;
        }

        if (!isset($map[$bucket])) {
            return [];
        }

        foreach ($img_list as $idx => $img) {
            if (substr($img, 0, 7) == 'http://') {
                continue;
            }
            if (substr($img, 0, 8) == 'https://') {
                continue;
            }
            if (substr($img, 0, 2) == '//') {
                continue;
            }
            $request_scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';

            // 强制刷新所有图片
            if (Helper::getParam('image_version')) {
                $img .= '?'.Helper::getParam('image_version');
            }

            $img_list[$idx] = $request_scheme.'://'.$map[$bucket].'/'.$img;
        }

        if ($private) {
            $auth = new Auth(Helper::getParam('qiniu_ak'), Helper::getParam('qiniu_sk'));
            foreach ($img_list as $idx => $img) {
                $img_list[$idx] = $auth->privateDownloadUrl($img, $expires);
            }
        }

        return empty($single) ? $img_list : $img_list[0];
    }

    /**
     * 上传图片到七牛
     * @param $file
     * @param $key
     * @param bool $private
     * @return bool|string[]
     * @throws \Exception
     */
    public static function upload($file, $key, $private = false) {
        $auth = new Auth(Helper::getParam('qiniu_ak'), Helper::getParam('qiniu_sk'));

        $private_bucket = Helper::getParam('qiniu_priv_bucket');
        $public_bucket = Helper::getParam('qiniu_pub_bucket');
        $bucket = $private ? $private_bucket : $public_bucket;

        $expires = 3600;
        $policy = [
            'returnBody' => '{"key":"$(key)","fsize":$(fsize)}',
        ];
        $upToken = $auth->uploadToken($bucket, null, $expires, $policy, true);

        $uploadMgr = new UploadManager();
        if (!file_exists($file)) {
            return ['error' => 'file not exists'];
        }

        list($ret, $err) = $uploadMgr->putFile($upToken, $key, $file);
        if ($err !== null) {
            $message = $err->message();
            return false;
        } else {
            return true;
        }
    }
}