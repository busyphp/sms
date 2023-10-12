<?php
declare(strict_types = 1);

namespace BusyPHP;

use BusyPHP\app\admin\model\system\config\SystemConfig;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\sms\Driver;
use InvalidArgumentException;
use think\Manager;

/**
 * 短信驱动
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午3:43 SmsManager.php $
 */
class Sms extends Manager
{
    protected $namespace = '\\BusyPHP\\sms\\driver\\';
    
    
    /**
     * 获取指定驱动
     * @param string|null $name
     */
    public function driver(string $name = null) : Driver
    {
        return parent::driver($name);
    }
    
    
    /**
     * 获取短信配置
     * @param null|string $name 名称
     * @param mixed|null  $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, mixed $default = null) : mixed
    {
        if (null !== $name) {
            return $this->app->config->get('sms.' . $name, $default);
        }
        
        return $this->app->config->get('sms');
    }
    
    
    /**
     * 获取短信驱动配置
     * @param string      $driver
     * @param string|null $name
     * @param mixed       $default
     * @return array
     */
    public function getDriverConfig(string $driver, string $name = null, mixed $default = null) : mixed
    {
        if ($config = $this->getConfig('drivers.' . $driver)) {
            return ArrayHelper::get(
                array_merge(
                    $config,
                    SystemConfig::init()->getSettingData($this->getSettingKey($driver))
                ),
                $name,
                $default
            );
        }
        
        throw new InvalidArgumentException("Driver [$driver] not found.");
    }
    
    
    /**
     * 获取设置Key
     * @param string $driver
     * @return string
     */
    public function getSettingKey(string $driver) : string
    {
        return 'plugin_sms_driver_' . StringHelper::snake($driver);
    }
    
    
    /**
     * 获取默认通道Key
     * @return string
     */
    public function getDefaultSettingKey() : string
    {
        return 'plugin_sms_default_driver';
    }
    
    
    protected function resolveType(string $name)
    {
        if ($class = $this->getDriverConfig($name, 'class')) {
            return $class;
        }
        
        return $this->getDriverConfig($name, 'type');
    }
    
    
    protected function resolveConfig(string $name)
    {
        return $this->getDriverConfig($name);
    }
    
    
    public function getDefaultDriver()
    {
        $data            = SystemConfig::init()->getSettingData($this->getDefaultSettingKey());
        $data['default'] = $data['default'] ?? '';
        if ($data['default']) {
            return $data['default'];
        }
        
        return $this->getConfig('default');
    }
}