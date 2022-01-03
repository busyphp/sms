<?php

namespace BusyPHP\sms\facade;

use think\Facade;

/**
 * 短信验证码工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/2 下午12:31 SmsCode.php $
 * @mixin \BusyPHP\sms\SmsCode
 * @method static string send(string $phone, string $type, ?int $shape = null, string $channel = '') 发送短信验证码
 * @method static void check(string $phone, string $type, string $code, bool $clear = true) 验证短信验证码
 * @method static void clear(string $phone, string $type) 清理短信验证码
 */
class SmsCode extends Facade
{
    protected static function getFacadeClass()
    {
        return \BusyPHP\sms\SmsCode::class;
    }
}