<?php

namespace BusyPHP\sms\facade;

use BusyPHP\sms\contract\SmsBatchSendData;
use BusyPHP\sms\contract\SmsInterface;
use BusyPHP\sms\SmsManager;
use think\Facade;

/**
 * 短信发送工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/2 下午12:10 Sms.php $
 * @mixin SmsManager
 * @method static mixed send(string|string[] $phone, string $template, array $params = [], string $attach = '') 单发/批量发送相同内容的短信
 * @method static mixed batchSend(SmsBatchSendData $data, string $template, string $attach = '') 批量发送不同内容的短信
 * @method static SmsInterface channel(string $name) 切换发送渠道
 */
class Sms extends Facade
{
    protected static function getFacadeClass()
    {
        return SmsManager::class;
    }
}