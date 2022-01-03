<?php

namespace BusyPHP\sms\driver\tencent;

use BusyPHP\model\ArrayOption;

/**
 * 腾讯云短信配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午4:09 TencentSmsConfig.php $
 * @property string $secret_id 腾讯云SecretId
 * @property string $secret_key 腾讯云SecretKey
 * @property string $sdk_app_id SDK AppID是短信应用的唯一标识，调用短信API接口时，需要提供该参数，在 短信控制台 添加应用后生成的实际 SdkAppId，示例如1400006666
 * @property string $app_key App Key是用来校验短信发送合法性的密码，与SDK AppID对应，需要业务方高度保密，切勿把密码存储在客户端
 * @property string $region 接入地域，如：ap-beijing
 * @property string $sign 短信签名
 * @property string $extend_code 短信码号扩展号，默认未开通，如需开通请联系 腾讯云短信小助手。
 * @property string $sender_id 国内短信无需填写该项；国际/港澳台短信已申请独立 SenderId 需要填写该字段，默认使用公共 SenderId，无需填写该字段
 */
class TencentSmsConfig extends ArrayOption
{
}