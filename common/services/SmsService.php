<?php

namespace common\services;

use common\helpers\Helper;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class SmsService
{
    /**
     * 发送阿里云短信
     * @param $mobile
     * @param $sign_name
     * @param $sms_tpl_code
     * @param $params
     * @return array
     * @throws ClientException
     */
    public static function sendSMS($mobile, $sign_name, $sms_tpl_code, $params)
    {
        $accessKeyId = Helper::getParam('ali_sms_ak');
        $accessKeySecret = Helper::getParam('ali_sms_sk');

        $params = json_encode($params, JSON_UNESCAPED_UNICODE);

        AlibabaCloud::accessKeyClient($accessKeyId, $accessKeySecret)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $mobile,
                        'SignName' => $sign_name,
                        'TemplateCode' => $sms_tpl_code,
                        'TemplateParam' => $params,
                    ],
                ])
                ->request();

            return $result->toArray();
        } catch (ClientException $exception) {
            return [
                'code' => 101,
                'msg' => $exception->getErrorMessage()
            ];
        } catch (ServerException $exception) {
            return [
                'code' => 102,
                'msg' => $exception->getErrorMessage()
            ];
        }
    }
}