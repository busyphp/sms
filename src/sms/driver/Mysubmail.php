<?php
declare(strict_types = 1);

namespace BusyPHP\sms\driver;

use BusyPHP\sms\Driver;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use RuntimeException;

/**
 * 赛邮云短信驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午5:52 Mysubmail.php $
 * @see https://www.mysubmail.com/documents/FppOR3
 */
class Mysubmail extends Driver
{
    protected string $appId;
    
    protected string $appKey;
    
    protected string $sign;
    
    protected string $internationalAppId;
    
    protected string $internationalAppKey;
    
    protected string $internationalSign;
    
    protected Client $client;
    
    
    public function __construct(array $config)
    {
        parent::__construct($config);
        
        $this->appId               = $this->config['app_id'] ?? '';
        $this->appKey              = $this->config['app_key'] ?? '';
        $this->sign                = $this->config['sign'] ?? '';
        $this->internationalAppId  = $this->config['international_app_id'] ?? '';
        $this->internationalAppKey = $this->config['international_app_key'] ?? '';
        $this->internationalSign   = $this->config['international_sign'] ?? '';
        
        $this->client = new Client();
    }
    
    
    /**
     * 内容模版是否模板ID
     * @return bool
     */
    public function isTemplateId() : bool
    {
        return false;
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
     * 获取设置表单配置
     * @return array
     */
    public function getSettingForm() : array
    {
        return [
            [
                'label'       => 'AppID',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'app_id',
                'required'    => true,
                'placeholder' => '请输入AppID',
                'help'        => '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取',
                'attributes'  => [
                    'data-msg-required' => '请输入AppID'
                ]
            ],
            [
                'label'       => 'AppKey',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'app_key',
                'required'    => true,
                'placeholder' => '请输入AppKey',
                'help'        => '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取',
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
                'help'        => '设置短信签名，如：函拓科技',
                'attributes'  => [
                    'data-msg-required' => '请输入短信签名'
                ]
            ],
            [
                'label'       => '国际短信AppID',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'international_app_id',
                'placeholder' => '请输入国际短信AppID',
                'help'        => '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取',
            ],
            [
                'label'       => '国际短信AppKey',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'international_app_key',
                'placeholder' => '请输入国际短信AppKey',
                'help'        => '登录 <a href="https://www.mysubmail.com/console/sms/apps" target="_blank">SUBMAIL</a> 创建AppID后获取',
            ],
            [
                'label'       => '国际短信签名',
                'tag'         => 'input',
                'type'        => 'text',
                'name'        => 'international_sign',
                'placeholder' => '请输入国际短信签名',
                'help'        => '设置国际短信签名，如：函拓科技',
            ],
        ];
    }
    
    
    /**
     * 单发/批量发送相同内容的短信
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param string $template 模板内容支持 `{变量名}` 变量
     * @param array  $vars 自定义变量键值对
     * @throws GuzzleException
     */
    protected function handleSend(string $no, string $template, array $vars = []) : array
    {
        foreach ($vars as $key => $value) {
            $template = str_replace(sprintf('{%s}', $key), $value, $template);
        }
        
        $params              = [];
        $params['appid']     = $this->appId;
        $params['signature'] = $this->appKey;
        $params['to']        = $no;
        
        // 国际号码走国内短信与国际短信联合发送
        // https://www.mysubmail.com/documents/HSX9F4
        if (!$this->isLocalNo($no)) {
            if (!$this->internationalAppId || !$this->internationalAppKey) {
                throw new RuntimeException('未配置国际短信渠道参数');
            }
            
            $params['inter_appid']     = $this->internationalAppId;
            $params['inter_signature'] = $this->internationalAppKey;
            if ($this->internationalSign) {
                $params['content'] = sprintf('[%s] %s', $this->internationalSign, $template);
            } else {
                $params['content'] = $template;
            }
            $url = 'https://api-v4.mysubmail.com/sms/unionsend.json';
        } else {
            $params['content'] = sprintf('【%s】%s', $this->sign, $template);
            $url               = 'https://api-v4.mysubmail.com/sms/send.json';
        }
        
        $response = $this->client->post($url, [RequestOptions::FORM_PARAMS => $params]);
        $result   = json_decode($response->getBody()->getContents(), true);
        if ($result['status'] != 'success') {
            throw new RuntimeException(sprintf("%s[%s]", $result['msg'], $result['code']));
        }
        
        return $result;
    }
}