<?php

namespace BusyPHP\sms;

use BusyPHP\App;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use BusyPHP\helper\ArrayHelper;
use Throwable;

/**
 * 短信配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午3:36 SmsConfig.php $
 */
trait SmsConfig
{
    /**
     * 获取短信配置
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getSmsConfig($name, $default = null)
    {
        return App::getInstance()->config->get('busy-sms' . ($name ? ".{$name}" : ''), $default);
    }
    
    
    /**
     * 获取短信设置
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getSmsSetting($name, $default = null)
    {
        try {
            $settings = SystemPlugin::init()->getSetting('busyphp/sms');
        } catch (Throwable $e) {
            $settings = [];
        }
        
        if (!$name) {
            return $settings;
        }
        
        return ArrayHelper::get($settings, $name, $default);
    }
    
    
    /**
     * 获取默认通道
     * @return string
     */
    public function getSmsDefaultChannel() : string
    {
        $default = $this->getSmsSetting('default');
        
        return $default ?: $this->getSmsConfig('default', '');
    }
    
    
    /**
     * 获取渠道配置
     * @param string $channel 渠道名称
     * @return array
     */
    public function getSmsChannelConfig(string $channel) : array
    {
        $config = $this->getSmsSetting("channels.{$channel}", []);
        if (!$config) {
            return $this->getSmsConfig("channels.{$channel}.config", []);
        }
        
        return $config;
    }
    
    
    /**
     * 获取短信模板内容或魔板ID
     * @param bool   $international 是否使用国际短信魔板
     * @param string $name 模板名称
     * @param string $channel 渠道名称
     * @return string
     */
    public function getSmsTemplate(bool $international, string $name, string $channel = '') : string
    {
        $channel = $channel ?: $this->getSmsDefaultChannel();
        
        // 优先获取国际短信模板
        $template = '';
        if ($international) {
            $template = $this->getSmsSetting("templates.$channel.{$name}_international", '');
        }
        
        // 没有国际短信模板获取常规短信模板
        if (!$template) {
            $template = $this->getSmsSetting("templates.$channel.$name", '');
        }
        
        // 获取渠道默认配置
        if (!$template) {
            // 优先获取国际短信模板
            if ($international) {
                $template = $this->getSmsConfig("channels.$channel.templates.{$name}_international", '');
            }
            
            // 获取默认设置
            if (!$template) {
                $template = $this->getSmsConfig("channels.$channel.templates.$name", '');
            }
        }
        
        return $template;
    }
    
    
    /**
     * 获取短信验证码账号类型
     * @return string
     */
    public function getSmsVerifyCodeAccountType() : string
    {
        $type = $this->getSmsConfig('verify_code.account_type', '');
        
        return $type ?: 'phone';
    }
}