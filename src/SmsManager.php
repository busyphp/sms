<?php

namespace BusyPHP\sms;

use BusyPHP\sms\contract\SmsInterface;
use BusyPHP\sms\driver\Tencent;
use think\Manager;

/**
 * 短信驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午3:43 Driver.php $
 * @mixin Tencent
 */
class SmsManager extends Manager
{
    use SmsConfig;
    
    protected $namespace = '\\BusyPHP\\sms\\driver\\';
    
    
    /**
     * 获取驱动配置
     * @param string $name
     * @return array
     */
    protected function resolveConfig(string $name)
    {
        return $this->getSmsChannelConfig($name);
    }
    
    
    /**
     * 切换短信通道
     * @param string $name
     * @return SmsInterface
     */
    public function channel(string $name = '') : SmsInterface
    {
        return $this->driver($name ? $this->getSmsConfig("channels.{$name}.driver") : null);
    }
    
    
    /**
     * 获取默认驱动
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->getSmsConfig("channels.{$this->getSmsDefaultChannel()}.driver");
    }
}