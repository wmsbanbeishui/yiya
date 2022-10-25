<?php

namespace common\helpers;

use Yii;
use yii\web\Response;

class CodeImage
{
    public static function run()
    {
        $code = self::generateVerifyCode();
        $origin_code = $code;

        $code = strtolower($code);

        Yii::$app->cache->set(self::generateCacheKey($code), $code, 60);

        self::setHttpHeaders();
        Yii::$app->response->format = Response::FORMAT_RAW;

        return self::renderImageByGD($origin_code);
    }

    /**
     * 获取验证码
     * @param int $min_length
     * @param int $max_length
     * @return string
     */
    protected static function generateVerifyCode($min_length = 4, $max_length = 4)
    {
        if ($min_length > $max_length) {
            $max_length = $min_length;
        }
        if ($min_length < 3) {
            $min_length = 3;
        }
        if ($max_length > 20) {
            $max_length = 20;
        }

        $length = mt_rand($min_length, $max_length);

        $letters = 'bcdfghjklmnpqrstvwxyz';
        $vowels = 'aeiou';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            if ($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9) {
                $code .= $vowels[mt_rand(0, 4)];
            } else {
                $code .= $letters[mt_rand(0, 20)];
            }
        }

        return $code;
    }

    /**
     * 返回图片
     * @param $code
     * @return false|string
     */
    protected static function renderImageByGD($code)
    {
        $width = 120;
        $height = 30;
        $offset = 6;
        $padding = 2;
        $backColor = 0xFFFFFF;
        $foreColor = 0x2040A0;
        $common_path = Yii::getAlias('@common');
        $fontFile = $common_path. '/SpicyRice.ttf';

        $image = imagecreatetruecolor($width, $height);

        $backColor = imagecolorallocate(
            $image,
            (int) ($backColor % 0x1000000 / 0x10000),
            (int) ($backColor % 0x10000 / 0x100),
            $backColor % 0x100
        );
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $backColor);
        imagecolordeallocate($image, $backColor);

        $foreColor = imagecolorallocate(
            $image,
            (int) ($foreColor % 0x1000000 / 0x10000),
            (int) ($foreColor % 0x10000 / 0x100),
            $foreColor % 0x100
        );

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $fontFile, $code);
        $w = $box[4] - $box[0] + $offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($width - $padding * 2) / $w, ($height - $padding * 2) / $h);
        $x = 10;
        $y = round($height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int) (mt_rand(26, 32) * $scale * 0.8);
            $angle = mt_rand(-10, 10);
            $letter = $code[$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $foreColor, $fontFile, $letter);
            $x = $box[2] + $offset;
        }

        imagecolordeallocate($image, $foreColor);

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return ob_get_clean();
    }

    protected static function setHttpHeaders()
    {
        Yii::$app->getResponse()->getHeaders()
            ->set('Pragma', 'public')
            ->set('Expires', '0')
            ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->set('Content-Transfer-Encoding', 'binary')
            ->set('Content-type', 'image/png');
    }

    /**
     * 获取验证码 key
     * @param $code
     * @return string
     */
    private static function generateCacheKey($code)
    {
        return base64_encode(Yii::$app->request->getRemoteIP().Yii::$app->request->getUserAgent().$code);
    }

    /**
     * 验证验证码
     * @param $code
     * @return bool
     */
    public static function validate($code)
    {
        $code = strtolower($code);
        if (Yii::$app->cache->get(self::generateCacheKey($code)) === $code) {
            Yii::$app->cache->delete(self::generateCacheKey($code));

            return true;
        }

        return false;
    }
}