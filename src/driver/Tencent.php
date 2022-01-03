<?php

namespace BusyPHP\sms\driver;

use BusyPHP\exception\VerifyException;
use BusyPHP\sms\contract\SmsBatchSendData;
use BusyPHP\sms\contract\SmsManagerFormItem;
use BusyPHP\sms\contract\SmsInterface;
use BusyPHP\sms\driver\tencent\TencentSmsConfig;
use RuntimeException;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\Models\SendStatus;
use TencentCloud\Sms\V20210111\SmsClient;

/**
 * 腾讯云短信驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午3:43 Tencent.php $
 * @see https://cloud.tencent.com/document/product/382/55981
 */
class Tencent implements SmsInterface
{
    /**
     * @var TencentSmsConfig
     */
    private $config;
    
    
    /**
     * Tencent constructor.
     * @param TencentSmsConfig $config
     */
    public function __construct(TencentSmsConfig $config)
    {
        $this->config         = $config;
        $this->config->region = $this->config->region ?: 'ap-beijing';
    }
    
    
    public static function __make(array $config)
    {
        return new self(TencentSmsConfig::init($config));
    }
    
    
    /**
     * 获取渠道名称
     * @return string
     */
    public function getName() : string
    {
        return '腾讯云';
    }
    
    
    /**
     * 获取主页
     * @return string
     */
    public function getHomepage() : string
    {
        return 'https://buy.cloud.tencent.com/sms';
    }
    
    
    /**
     * 获取管理表单
     * @return SmsManagerFormItem[]
     */
    public function getManagerForms() : array
    {
        $forms   = [];
        $forms[] = new SmsManagerFormItem('secret_id', 'SecretId', '登录 <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">腾讯云</a> API密钥管理中查看');
        $forms[] = new SmsManagerFormItem('secret_key', 'SecretKey', '登录 <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">腾讯云</a> API密钥管理中查看');
        $forms[] = new SmsManagerFormItem('sdk_app_id', 'SdkAppId', '登录 <a href="https://buy.cloud.tencent.com/sms" target="_blank">腾讯云短信控制台</a> 添加应用后生成的实际 SdkAppId');
        $forms[] = new SmsManagerFormItem('app_key', 'AppKey', '登录 <a href="https://buy.cloud.tencent.com/sms" target="_blank">腾讯云短信控制台</a> 添加应用后生成的实际 AppKey');
        $forms[] = new SmsManagerFormItem('sign', '短信签名', '填写腾讯云审核后的短信签名，如：函拓科技');
        
        $region = new SmsManagerFormItem('region', '接入地域', '');
        $region->setSelect([
            'ap-beijing'   => '华北地区(北京)',
            'ap-guangzhou' => '华南地区(广州)',
            'ap-nanjing'   => '华东地区(南京)',
        ]);
        $forms[] = $region;
        $forms[] = new SmsManagerFormItem('extend_code', '短信码号扩展号', '默认未开通，如需开通请联系 <a href="https://cloud.tencent.com/document/product/382/3773#.E6.8A.80.E6.9C.AF.E4.BA.A4.E6.B5.81">腾讯云短信小助手</a>', false);
        $forms[] = new SmsManagerFormItem('sender_id', 'SenderId', '国内短信无需填写该项；国际/港澳台短信已申请独立 SenderId 需要填写该字段，默认使用公共 SenderId，无需填写该字段<br />注：月度使用量达到指定量级可申请独立 SenderId 使用，详情请联系 <a href="https://cloud.tencent.com/document/product/382/3773#.E6.8A.80.E6.9C.AF.E4.BA.A4.E6.B5.81">腾讯云短信小助手</a>', false);
        
        return $forms;
    }
    
    
    /**
     * 发送短信
     * @param string|string[] $phone 手机号，如：+8613333333333 或 [13333333333, +8613333333333]
     * @param string          $template 魔板ID
     * @param array           $vars 自定义变量键值对
     * @param string          $attach 自定义附加数据，server会原样返回
     * @return SendStatus|SendStatus[]
     * @throws TencentCloudSDKException
     */
    public function send($phone, string $template, array $vars = [], string $attach = '')
    {
        if (!$phone) {
            throw new VerifyException('发送手机号不能为空', 'phone');
        }
        
        // 补齐+86
        $phones = is_array($phone) ? $phone : [$phone];
        foreach ($phones as $i => $phone) {
            if (false === strpos($phone, '+')) {
                $phones[$i] = "+86{$phone}";
            }
        }
        
        $cred        = new Credential($this->config->secret_id, $this->config->secret_key);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("sms.tencentcloudapi.com");
        
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new SmsClient($cred, $this->config->region, $clientProfile);
        
        $args = [];
        foreach ($vars as $param) {
            $args[] = $param;
        }
        
        $req = new SendSmsRequest();
        $req->setSmsSdkAppId($this->config->sdk_app_id);
        $req->setTemplateId($template);
        $req->setTemplateParamSet($args);
        $req->setPhoneNumberSet($phones);
        $req->setSignName($this->config->sign ?: '');
        $req->setExtendCode($this->config->extend_code ?: '');
        $req->setSessionContext($attach ?: '');
        $req->setSenderId($this->config->sender_id ?: '');
        $res = $client->SendSms($req);
        
        // 单发
        if (count($phones) == 1) {
            $status = $res->getSendStatusSet()[0] ?? null;
            if (!$status instanceof SendStatus) {
                throw new TencentCloudSDKException('', '发送失败', $res->getRequestId());
            }
            
            if ($status->getCode() != 'Ok') {
                throw new TencentCloudSDKException($status->getCode(), $status->getMessage(), $res->getRequestId());
            }
            
            return $status;
        }
        
        return $res->getSendStatusSet();
    }
    
    
    /**
     * 批量发送短信
     * @param SmsBatchSendData $data 数据对象
     * @param string           $template 魔板ID或魔板内容，魔板内容支持 `{变量名}` 变量
     * @param string           $attach 自定义附加数据，server会原样返回
     * @return mixed
     */
    public function batchSend(SmsBatchSendData $data, string $template, string $attach = '')
    {
        throw new RuntimeException('腾讯云短信不支持批量发送');
    }
}