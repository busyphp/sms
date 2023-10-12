<?php
declare(strict_types = 1);

namespace BusyPHP\facade;

use BusyPHP\sms\Driver;
use think\Facade;

/**
 * 短信发送工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/2 下午12:10 Sms.php $
 * @mixin \BusyPHP\Sms
 * @method static Driver driver(string $name = null) 切换发送渠道
 * @method static mixed getConfig(string $name = null, mixed $default = null) 获取配置
 * @method static mixed getDriverConfig(string $driver, string $name = null, mixed $default = null) 获取指定驱动配置
 * @method static string getSettingKey(string $name) 获取后台设置驱动配置键名
 * @method static string getDefaultSettingKey() 获取后台默认通道配置键名
 * @method static string getDefaultDriver() 获取默认通道名称
 */
class Sms extends Facade
{
    protected static function getFacadeClass()
    {
        return \BusyPHP\Sms::class;
    }
}