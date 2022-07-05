<?php

namespace BusyPHP\sms\driver;

use BusyPHP\exception\VerifyException;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\HttpHelper;
use BusyPHP\sms\contract\SmsBatchSendData;
use BusyPHP\sms\contract\SmsManagerFormItem;
use BusyPHP\sms\contract\SmsInterface;
use BusyPHP\sms\driver\mysubmail\MysubmailSmsConfig;
use BusyPHP\sms\driver\mysubmail\MysubmailSmsSendStatus;
use BusyPHP\sms\SmsConfig;
use RuntimeException;

/**
 * 赛邮云短信驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午5:52 Mysubmail.php $
 * @see https://www.mysubmail.com/documents/FppOR3
 */
class Mysubmail implements SmsInterface
{
    use SmsConfig;
    
    /**
     * @var MysubmailSmsConfig
     */
    private $config;
    
    /**
     * 是否启用国际短信
     * @var bool
     */
    private $international;
    
    
    /**
     * Mysubmail constructor.
     * @param MysubmailSmsConfig $config
     */
    public function __construct(MysubmailSmsConfig $config)
    {
        $this->config        = $config;
        $this->international = $this->getSmsConfig('channels.mysubmail.international', false);
    }
    
    
    public static function __make(array $config)
    {
        return new self(MysubmailSmsConfig::init($config));
    }
    
    
    /**
     * 签名
     * @param string $appId
     * @param string $appKey
     * @param array  $params
     * @return string
     */
    protected function sign(string $appId, string $appKey, array $params) : string
    {
        ksort($params);
        reset($params);
        $temp = [];
        foreach ($params as $key => $v) {
            $temp[] = "{$key}={$v}";
        }
        $temp = implode('&', $temp);
        
        return md5("{$appId}{$appKey}{$temp}{$appId}{$appKey}");
    }
    
    
    /**
     * 单发短信
     * @param string $phone 手机号
     * @param string $template 模板内容
     * @param array  $vars 模板变量
     * @return MysubmailSmsSendStatus
     * @see https://www.mysubmail.com/documents/FppOR3
     * @see https://www.mysubmail.com/documents/3UQA3
     */
    public function singleSend(string $phone, string $template, array $vars = [])
    {
        foreach ($vars as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }
        
        $appId         = $this->config->app_id;
        $appKey        = $this->config->app_key;
        $appSign       = $this->config->sign;
        $international = 0 === strpos($phone, '+') && false === strpos($phone, '+86');
        if ($this->international && $international) {
            $appId   = $this->config->international_app_id;
            $appKey  = $this->config->international_app_key;
            $appSign = $this->config->international_sign ?: $appSign;
        }
        
        $params              = [];
        $params['appid']     = $appId;
        $params['to']        = $phone;
        $params['content']   = "【{$appSign}】{$template}";
        $params['timestamp'] = time();
        $params['sign_type'] = 'md5';
        $params['signature'] = $this->sign($appId, $appKey, $params);
        
        if (0 === strpos($phone, '+86')) {
            $phone = substr($phone, 3);
        }
        
        // 国际短信
        $result = HttpHelper::post($international ? 'https://api-v4.mysubmail.com/internationalsms/send.json' : 'https://api-v4.mysubmail.com/sms/send.json', $params);
        $result = json_decode($result, true);
        if ($result['status'] != 'success') {
            throw new RuntimeException("{$result['msg']}[{$result['code']}]");
        }
        
        return MysubmailSmsSendStatus::init($result);
    }
    
    
    /**
     * 获取主页
     * @return string
     */
    public function getHomepage() : string
    {
        return 'https://www.mysubmail.com/sms';
    }
    
    
    /**
     * 获取渠道名称
     * @return string
     */
    public function getName() : string
    {
        return 'SUBMAIL赛邮';
    }
    
    
    /**
     * 获取管理表单
     * @return SmsManagerFormItem[]
     */
    public function getManagerForms() : array
    {
        $forms   = [];
        $forms[] = new SmsManagerFormItem('app_id', 'AppID', '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取');
        $forms[] = new SmsManagerFormItem('app_key', 'Appkey', '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取');
        $forms[] = new SmsManagerFormItem('sign', '短信签名', '设置短信签名，如：函拓科技');
        
        if ($this->international) {
            $forms[] = new SmsManagerFormItem('international_app_id', '国际短信AppID', '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取');
            $forms[] = new SmsManagerFormItem('international_app_key', '国际短信Appkey', '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取');
            $forms[] = new SmsManagerFormItem('international_sign', '国际短信签名', '设置短信签名，如：函拓科技，不设置将使用国内短信签名', false);
        }
        
        
        return $forms;
    }
    
    
    /**
     * 单发/批量发送相同内容的短信
     * @param string|string[] $phone 手机号，如：+8613333333333 或 [13333333333, +8613333333333]
     * @param string          $template 魔板ID或魔板内容，魔板内容支持 `{变量名}` 变量
     * @param array           $vars 自定义变量键值对
     * @param string          $attach 自定义附加数据，server会原样返回
     * @return MysubmailSmsSendStatus|MysubmailSmsSendStatus[]
     */
    public function send($phone, string $template, array $vars = [], string $attach = '')
    {
        if (!$phone) {
            throw new VerifyException('发送手机号不能为空', 'phone');
        }
        
        if (is_array($phone) && count($phone) > 1) {
            $data = new SmsBatchSendData();
            foreach ($phone as $item) {
                $data->add($item, $vars);
            }
            
            return $this->batchSend($data, $template, $attach);
        }
        
        return $this->singleSend(is_array($phone) ? $phone[0] : $phone, $template, $vars);
    }
    
    
    /**
     * 批量发送不同内容的短信
     * @param SmsBatchSendData $data 数据对象
     * @param string           $template 魔板ID或魔板内容，魔板内容支持 `{变量名}` 变量
     * @param string           $attach 自定义附加数据，server会原样返回
     * @return mixed
     */
    public function batchSend(SmsBatchSendData $data, string $template, string $attach = '')
    {
        $list = $data->getList();
        if (!$list) {
            throw new VerifyException('发送数据不能为空', 'data');
        }
        
        if (count($list) > 200) {
            throw new VerifyException('批量发送上限为200个手机号码');
        }
        
        foreach ($list[0]['vars'] as $key => $value) {
            $template = str_replace("{{$key}}", "@var({$key})", $template);
        }
        
        $multi = [];
        foreach ($list as $item) {
            $phone = $item['phone'];
            if (0 === strpos($phone, '+86')) {
                $phone = substr($phone, 3);
            }
            
            if (0 === strpos($phone, '+')) {
                throw new RuntimeException('国际短信不支持批量发送');
            }
            
            $multi[] = [
                'to'   => $phone,
                'vars' => $item['vars']
            ];
        }
        
        $params              = [];
        $params['appid']     = $this->config->app_id;
        $params['content']   = "【{$this->config->sign}】{$template}";
        $params['multi']     = json_encode($multi, JSON_UNESCAPED_UNICODE);
        $params['timestamp'] = time();
        $params['sign_type'] = 'md5';
        $params['signature'] = $this->sign($this->config->app_id, $this->config->app_key, $params);
        
        $result = HttpHelper::post('https://api-v4.mysubmail.com/sms/batchsend.json', $params);
        $result = json_decode($result, true) ?: [];
        if (ArrayHelper::isAssoc($result)) {
            throw new RuntimeException(($result['msg'] ?? '未知错误') . '[' . ($result['code'] ?? 0) . ']');
        }
        
        $status = [];
        foreach ($result as $item) {
            $status[] = MysubmailSmsSendStatus::init($item);
        }
        
        return $status;
    }
}