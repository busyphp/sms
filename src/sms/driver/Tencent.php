<?php
declare(strict_types = 1);

namespace BusyPHP\sms\driver;

use BusyPHP\sms\Driver;
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
class Tencent extends Driver
{
    protected string $secretId;
    
    protected string $secretKey;
    
    protected string $sdkAppId;
    
    protected string $appKey;
    
    protected string $sign;
    
    protected string $region;
    
    protected string $extendCode;
    
    protected string $senderId;
    
    
    public function __construct(array $config)
    {
        parent::__construct($config);
        
        $this->secretId   = $this->config['secret_id'] ?? '';
        $this->secretKey  = $this->config['secret_key'] ?? '';
        $this->sdkAppId   = $this->config['sdk_app_id'] ?? '';
        $this->appKey     = $this->config['app_key'] ?? '';
        $this->sign       = $this->config['sign'] ?? '';
        $this->region     = $this->config['region'] ?? '';
        $this->extendCode = $this->config['extend_code'] ?? '';
        $this->senderId   = $this->config['sender_id'] ?? '';
        
        $this->region = $this->region ?: 'ap-beijing';
    }
    
    
    /**
     * 内容模版是否模板ID
     * @return bool
     */
    public function isTemplateId() : bool
    {
        return true;
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
     * 获取设置表单配置
     * @return array
     */
    public function getSettingForm() : array
    {
        return [
            [
                'label'       => 'SecretId',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'secret_id',
                'required'    => true,
                'placeholder' => '请输入SecretId',
                'help'        => '登录 <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">腾讯云</a> API密钥管理中查看',
                'attributes'  => [
                    'data-msg-required' => '请输入SecretId'
                ]
            ],
            [
                'label'       => 'SecretKey',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'secret_key',
                'required'    => true,
                'placeholder' => '请输入SecretKey',
                'help'        => '登录 <a href="https://console.cloud.tencent.com/cam/capi" target="_blank">腾讯云</a> API密钥管理中查看',
                'attributes'  => [
                    'data-msg-required' => '请输入SecretKey'
                ]
            ],
            [
                'label'       => 'SdkAppId',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'sdk_app_id',
                'required'    => true,
                'placeholder' => '请输入SdkAppId',
                'help'        => '登录 <a href="https://buy.cloud.tencent.com/sms" target="_blank">腾讯云短信控制台</a> 添加应用后生成的实际 SdkAppId',
                'attributes'  => [
                    'data-msg-required' => '请输入SdkAppId'
                ]
            ],
            [
                'label'       => 'AppKey',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'app_key',
                'required'    => true,
                'placeholder' => '请输入AppKey',
                'help'        => '登录 <a href="https://buy.cloud.tencent.com/sms" target="_blank">腾讯云短信控制台</a> 添加应用后生成的实际 AppKey',
                'attributes'  => [
                    'data-msg-required' => '请输入AppKey'
                ]
            ],
            [
                'label'       => '短信签名',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'sign',
                'required'    => true,
                'placeholder' => '请输入短信签名',
                'help'        => '填写腾讯云审核后的短信签名，如：函拓科技',
                'attributes'  => [
                    'data-msg-required' => '请输入短信签名'
                ]
            ],
            [
                'label'   => '接入地域',
                'tag'     => 'select',
                'name'    => 'region',
                'options' => [
                    ['value' => '', 'text' => '默认'],
                    ['value' => 'ap-beijing', 'text' => '华北地区(北京)'],
                    ['value' => 'ap-guangzhou', 'text' => '华南地区(广州)'],
                    ['value' => 'ap-nanjing', 'text' => '华东地区(南京)'],
                ],
                'help'    => '请选择接入地域',
            ],
            [
                'label'       => '短信码号扩展号',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'extend_code',
                'placeholder' => '请输入短信码号扩展号',
                'help'        => '默认未开通，如需开通请联系 <a href="https://cloud.tencent.com/document/product/382/3773#.E6.8A.80.E6.9C.AF.E4.BA.A4.E6.B5.81">腾讯云短信小助手</a>',
            ],
            [
                'label'       => 'SenderId',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'sender_id',
                'placeholder' => '请输入SenderId',
                'help'        => '国内短信无需填写该项；国际/港澳台短信已申请独立 SenderId 需要填写该字段，默认使用公共 SenderId，无需填写该字段<br />注：月度使用量达到指定量级可申请独立 SenderId 使用，详情请联系 <a href="https://cloud.tencent.com/document/product/382/3773#.E6.8A.80.E6.9C.AF.E4.BA.A4.E6.B5.81">腾讯云短信小助手',
            ],
        ];
    }
    
    
    /**
     * 发送短信
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param string $template 模板ID
     * @param array  $vars 自定义变量键值对
     * @return SendStatus
     * @throws TencentCloudSDKException
     */
    protected function handleSend(string $no, string $template, array $vars = []) : SendStatus
    {
        $cred        = new Credential($this->secretId, $this->secretKey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("sms.tencentcloudapi.com");
        
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new SmsClient($cred, $this->region, $clientProfile);
        
        $args = [];
        foreach ($vars as $param) {
            $args[] = $param;
        }
        
        $req = new SendSmsRequest();
        $req->setSmsSdkAppId($this->sdkAppId);
        $req->setTemplateId($template);
        $req->setTemplateParamSet($args);
        $req->setPhoneNumberSet([$this->coverNo($no)]);
        $req->setSignName($this->sign ?: '');
        $req->setExtendCode($this->extendCode ?: '');
        $req->setSenderId($this->senderId ?: '');
        $res = $client->SendSms($req);
        
        $status = $res->getSendStatusSet()[0] ?? null;
        if (!$status instanceof SendStatus) {
            throw new TencentCloudSDKException('', '发送失败', $res->getRequestId());
        }
        
        if ($status->getCode() != 'Ok') {
            throw new TencentCloudSDKException($status->getCode(), $status->getMessage(), $res->getRequestId());
        }
        
        return $status;
    }
}