<?php

namespace BusyPHP\sms\driver;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchSmsResponseBody;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsResponseBody;
use BusyPHP\exception\VerifyException;
use BusyPHP\sms\contract\SmsBatchSendData;
use BusyPHP\sms\contract\SmsManagerFormItem;
use BusyPHP\sms\contract\SmsInterface;
use BusyPHP\sms\driver\aliyun\AliyunSmsConfig;
use Darabonba\OpenApi\Models\Config;
use RuntimeException;

/**
 * 阿里云短信
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/3 下午3:37 Aliyun.php $
 */
class Aliyun implements SmsInterface
{
    /**
     * @var AliyunSmsConfig
     */
    private $config;
    
    
    public function __construct(AliyunSmsConfig $config)
    {
        $this->config         = $config;
        $this->config->region = $this->config->region ?: 'cn-beijing';
    }
    
    
    public static function __make(array $config)
    {
        return new self(AliyunSmsConfig::init($config));
    }
    
    
    /**
     * @return Dysmsapi
     */
    protected function createClient() : Dysmsapi
    {
        $config                  = new Config();
        $config->accessKeyId     = $this->config->access_key_id;
        $config->accessKeySecret = $this->config->access_key_secret;
        $config->endpoint        = 0 === stripos($this->config->region, 'cn-') ? 'dysmsapi.aliyuncs.com' : 'dysmsapi.ap-southeast-1.aliyuncs.com';
        $config->regionId        = $this->config->region;
        
        return new Dysmsapi($config);
    }
    
    
    /**
     * 获取主页
     * @return string
     */
    public function getHomepage() : string
    {
        return 'https://www.aliyun.com/product/sms';
    }
    
    
    /**
     * 获取渠道名称
     * @return string
     */
    public function getName() : string
    {
        return '阿里云';
    }
    
    
    /**
     * 获取管理表单
     * @return SmsManagerFormItem[]
     */
    public function getManagerForms() : array
    {
        $forms   = [];
        $forms[] = new SmsManagerFormItem('access_key_id', 'AccessKeyId', '登录 <a href="https://ram.console.aliyun.com/manage/ak" target="_blank">阿里云RAM访问控制</a> 获取');
        $forms[] = new SmsManagerFormItem('access_key_secret', 'AccessKeySecret', '登录 <a href="https://ram.console.aliyun.com/manage/ak" target="_blank">阿里云RAM访问控制</a> 获取');
        $forms[] = new SmsManagerFormItem('sign', '短信签名', '填写阿里云审核后的短信签名，如：函拓科技');
        $region  = new SmsManagerFormItem('region', '接入地域', '');
        $region->setSelect([
            'cn-qingdao'            => '华北1（青岛）',
            'cn-beijing'            => '华北2（北京）',
            'cn-zhangjiakou'        => '华北3（张家口）',
            'cn-huhehaote'          => '华北5（呼和浩特）',
            'cn-wulanchabu'         => '华北6（乌兰察布）',
            'cn-hangzhou'           => '华东1（杭州）',
            'cn-shanghai'           => '华东2（上海）',
            'cn-shenzhen'           => '华南1（深圳）',
            'cn-chengdu'            => '西南1（成都）',
            'cn-hongkong'           => '中国（香港）',
            'cn-hangzhou-finance'   => '华东1（金融云）',
            'cn-shanghai-finance-1' => '华东2（金融云）',
            'cn-shenzhen-finance-1' => '华南1（金融云）',
            'ap-northeast-1'        => '日本（东京）',
            'ap-southeast-1'        => '新加坡',
            'ap-southeast-2'        => '澳大利亚（悉尼）',
            'ap-southeast-3'        => '马来西亚（吉隆坡）',
            'ap-southeast-5'        => '印度尼西亚（雅加达）',
            'us-east-1'             => '美国（弗吉尼亚）',
            'us-west-1'             => '美国（硅谷）',
            'eu-west-1'             => '英国（伦敦）',
            'eu-central-1'          => '德国（法兰克福）',
            'ap-south-1'            => '印度（孟买）',
            'me-east-1'             => '阿联酋（迪拜）',
        ]);
        $forms[] = $region;
        
        return $forms;
    }
    
    
    /**
     * 发送短信
     * @param string|string[] $phone 手机号，如：+8613333333333 或 [13333333333, +8613333333333]
     * @param string          $template 魔板ID或魔板内容，魔板内容支持 `{变量名}` 变量
     * @param array           $vars 自定义变量键值对
     * @param string          $attach 自定义附加数据，server会原样返回
     * @return SendSmsResponseBody
     * @see https://help.aliyun.com/document_detail/101414.html
     */
    public function send($phone, string $template, array $vars = [], string $attach = '') : SendSmsResponseBody
    {
        if (!$phone) {
            throw new VerifyException('发送手机号不能为空', 'phone');
        }
        
        $phones = is_array($phone) ? $phone : [$phone];
        if (count($phones) > 1000) {
            throw new RuntimeException('批量发送上限为1000个手机号码');
        }
        
        foreach ($phones as $i => $phone) {
            if (0 === strpos($phone, '+86')) {
                $phones[$i] = substr($phone, 3);
            }
        }
        
        $req                = new SendSmsRequest();
        $req->phoneNumbers  = implode(',', $phones);
        $req->signName      = $this->config->sign;
        $req->templateCode  = $template;
        $req->templateParam = json_encode($vars, JSON_UNESCAPED_UNICODE);
        
        $res = $this->createClient()->sendSms($req)->body;
        if ($res->code != 'OK') {
            throw new RuntimeException("{$res->message}[{$res->code}]");
        }
        
        return $res;
    }
    
    
    /**
     * 批量发送短信
     * @param SmsBatchSendData $data 数据对象
     * @param string           $template 魔板ID或魔板内容，魔板内容支持 `{变量名}` 变量
     * @param string           $attach 自定义附加数据，server会原样返回
     * @return SendBatchSmsResponseBody
     * @see https://help.aliyun.com/document_detail/102364.html
     */
    public function batchSend(SmsBatchSendData $data, string $template, string $attach = '') : SendBatchSmsResponseBody
    {
        $list = $data->getList();
        if (!$list) {
            throw new VerifyException('发送手机号不能为空', 'phone');
        }
        
        $phones = [];
        $signs  = [];
        $params = [];
        foreach ($list as $item) {
            $phone = $item['phone'];
            if (false === strpos($phone, '+')) {
                $phone = "+86{$phone}";
            }
            
            $phones[] = $phone;
            $signs[]  = $this->config->sign;
            $params[] = $item['vars'];
        }
        
        $req                    = new SendBatchSmsRequest();
        $req->phoneNumberJson   = json_encode($phones, JSON_UNESCAPED_UNICODE);
        $req->signNameJson      = json_encode($signs, JSON_UNESCAPED_UNICODE);
        $req->templateCode      = $template;
        $req->templateParamJson = json_encode($params, JSON_UNESCAPED_UNICODE);
        
        $res = $this->createClient()->sendBatchSms($req)->body;
        if ($res->code != 'OK') {
            throw new RuntimeException("{$res->message}[{$res->code}]");
        }
        
        return $res;
    }
}