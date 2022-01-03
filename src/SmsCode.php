<?php

namespace BusyPHP\sms;

use BusyPHP\sms\facade\Sms;
use BusyPHP\verifycode\facade\VerifyCode;
use RuntimeException;
use Throwable;

/**
 * 短信验证码
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/2 上午11:59 SmsCode.php $
 */
class SmsCode
{
    use SmsConfig;
    
    /**
     * 发送短信验证码
     * @param string $phone 手机号
     * @param string $type 短信类型
     * @param int    $shape 验证码形式，默认为数字
     * @param string $channel 发送短信渠道
     * @return string 发送成功的短信验证码
     * @throws Throwable
     */
    public function send(string $phone, string $type, ?int $shape = null, string $channel = '') : string
    {
        $template = $this->getSmsTemplate($type, $channel);
        if (!$template) {
            throw new RuntimeException('未配置短信模板');
        }
        
        // 生成短信验证码
        $accountType = $this->getSmsVerifyCodeAccountType();
        $code        = VerifyCode::create($accountType, $phone, $type, $shape);
        
        // 发送短信验证码
        try {
            Sms::channel($channel)->send($phone, $template, [
                'code' => $code
            ]);
        } catch (Throwable $e) {
            VerifyCode::clear($accountType, $phone, $type);
            
            throw $e;
        }
        
        return $code;
    }
    
    
    /**
     * 校验短信验证码
     * @param string $phone 手机号
     * @param string $type 短信类型
     * @param string $code 验证码
     * @param bool   $clear 验证成功是否清理
     */
    public function check(string $phone, string $type, string $code, bool $clear = true)
    {
        VerifyCode::check($this->getSmsVerifyCodeAccountType(), $phone, $type, $code, $clear);
    }
    
    
    /**
     * 清理短信验证码
     * @param string $phone 手机号
     * @param string $type 短信类型
     */
    public function clear(string $phone, string $type)
    {
        VerifyCode::clear($this->getSmsVerifyCodeAccountType(), $phone, $type);
    }
}