<?php
declare(strict_types = 1);

namespace BusyPHP\sms\driver;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsResponseBody;
use BusyPHP\sms\Driver;
use Darabonba\OpenApi\Models\Config;
use RuntimeException;

/**
 * 阿里云短信
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/3 下午3:37 Aliyun.php $
 */
class Aliyun extends Driver
{
    protected string $accessKeyId;
    
    protected string $accessKeySecret;
    
    protected string $region;
    
    protected string $sign;
    
    
    public function __construct(array $config)
    {
        parent::__construct($config);
        
        $this->accessKeyId     = $this->config['access_key_id'] ?? '';
        $this->accessKeySecret = $this->config['access_key_secret'] ?? '';
        $this->region          = $this->config['region'] ?? '';
        $this->sign            = $this->config['sign'] ?? '';
        
        $this->region = $this->region ?: 'cn-beijing';
    }
    
    
    /**
     * @return Dysmsapi
     */
    protected function createClient() : Dysmsapi
    {
        $config                  = new Config();
        $config->accessKeyId     = $this->accessKeyId;
        $config->accessKeySecret = $this->accessKeySecret;
        $config->endpoint        = 0 === stripos($this->region, 'cn-') ? 'dysmsapi.aliyuncs.com' : 'dysmsapi.ap-southeast-1.aliyuncs.com';
        $config->regionId        = $this->region;
        
        return new Dysmsapi($config);
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
     * 获取设置表单配置
     * @return array
     */
    public function getSettingForm() : array
    {
        return [
            [
                'label'       => 'AccessKeyId',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'access_key_id',
                'required'    => true,
                'placeholder' => '请输入AccessKeyId',
                'help'        => '登录 <a href="https://ram.console.aliyun.com/manage/ak" target="_blank">阿里云RAM访问控制</a> 获取',
                'attributes'  => [
                    'data-msg-required' => '请输入AccessKeyId'
                ]
            ],
            [
                'label'       => 'AccessKeySecret',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'access_key_secret',
                'required'    => true,
                'placeholder' => '请输入AccessKeySecret',
                'help'        => '登录 <a href="https://ram.console.aliyun.com/manage/ak" target="_blank">阿里云RAM访问控制</a> 获取',
                'attributes'  => [
                    'data-msg-required' => '请输入AccessKeySecret'
                ]
            ],
            [
                'label'       => '短信签名',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'sign',
                'required'    => true,
                'placeholder' => '请输入短信签名',
                'help'        => '填写阿里云审核后的短信签名，如：函拓科技',
                'attributes'  => [
                    'data-msg-required' => '请输入AccessKeySecret'
                ]
            ],
            [
                'label'   => '接入地域',
                'tag'     => 'select',
                'type'    => 'text',
                'name'    => 'region',
                'options' => [
                    ['value' => '', 'text' => '默认'],
                    ['value' => 'cn-qingdao', 'text' => '华北1（青岛）'],
                    ['value' => 'cn-beijing', 'text' => '华北2（北京）'],
                    ['value' => 'cn-zhangjiakou', 'text' => '华北3（张家口）'],
                    ['value' => 'cn-huhehaote', 'text' => '华北5（呼和浩特）'],
                    ['value' => 'cn-wulanchabu', 'text' => '华北6（乌兰察布）'],
                    ['value' => 'cn-hangzhou', 'text' => '华东1（杭州）'],
                    ['value' => 'cn-shanghai', 'text' => '华东2（上海）'],
                    ['value' => 'cn-shenzhen', 'text' => '华南1（深圳）'],
                    ['value' => 'cn-chengdu', 'text' => '西南1（成都）'],
                    ['value' => 'cn-hongkong', 'text' => '中国（香港）'],
                    ['value' => 'cn-hongkong', 'text' => '中国（香港）'],
                    ['value' => 'cn-hangzhou-finance', 'text' => '华东1（金融云）'],
                    ['value' => 'cn-shanghai-finance-1', 'text' => '华东2（金融云）'],
                    ['value' => 'cn-shenzhen-finance-1', 'text' => '华南1（金融云）'],
                    ['value' => 'ap-northeast-1', 'text' => '日本（东京）'],
                    ['value' => 'ap-southeast-1', 'text' => '新加坡'],
                    ['value' => 'ap-southeast-2', 'text' => '澳大利亚（悉尼）'],
                    ['value' => 'ap-southeast-3', 'text' => '马来西亚（吉隆坡）'],
                    ['value' => 'ap-southeast-5', 'text' => '印度尼西亚（雅加达）'],
                    ['value' => 'us-east-1', 'text' => '美国（弗吉尼亚）'],
                    ['value' => 'us-west-1', 'text' => '美国（硅谷）'],
                    ['value' => 'eu-west-1', 'text' => '英国（伦敦）'],
                    ['value' => 'eu-central-1', 'text' => '德国（法兰克福）'],
                    ['value' => 'ap-south-1', 'text' => '印度（孟买）'],
                    ['value' => 'me-east-1', 'text' => '阿联酋（迪拜）'],
                ],
                'help'    => '请选择接入地域',
            ]
        ];
    }
    
    
    /**
     * 发送短信
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param string $content 魔板ID
     * @param array  $vars 自定义变量键值对
     * @return SendSmsResponseBody
     * @see https://help.aliyun.com/document_detail/101414.html
     */
    protected function handle(string $no, string $content, array $vars = []) : SendSmsResponseBody
    {
        $req                = new SendSmsRequest();
        $req->phoneNumbers  = $no;
        $req->signName      = $this->sign;
        $req->templateCode  = $content;
        $req->templateParam = json_encode($vars, JSON_UNESCAPED_UNICODE);
        
        $res = $this->createClient()->sendSms($req)->body;
        if ($res->code != 'OK') {
            throw new RuntimeException(sprintf("%s[%s]", $res->message, $res->code));
        }
        
        return $res;
    }
}