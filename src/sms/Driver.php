<?php
declare(strict_types = 1);

namespace BusyPHP\sms;

use BusyPHP\exception\VerifyException;
use BusyPHP\facade\Sms;
use BusyPHP\facade\VerifyCode;
use RuntimeException;
use think\facade\Lang;
use Throwable;

/**
 * 短信发送驱动类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/10/11 16:28 Driver.php $
 */
abstract class Driver
{
    /**
     * 配置参数
     * @var array
     */
    protected array $config = [
        // 必须，驱动类型，如：aliyun，如果不设置class则默认使用 BusyPHP\sms\driver\Aliyun 作为驱动类
        'type'  => '',
        
        // 可选，驱动类，设置后驱动类按照该类
        'class' => '',
    ];
    
    
    /**
     * 构造函数
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }
    
    
    /**
     * 转换手机号为包含国际代码的号码
     * @param string $no
     * @return string
     */
    protected function coverNo(string $no) : string
    {
        if (!str_starts_with($no, '+')) {
            return '+86' . $no;
        }
        
        return $no;
    }
    
    
    /**
     * 判断是否本地号码
     * @param string $no
     * @return bool
     */
    protected function isLocalNo(string $no) : bool
    {
        return str_starts_with($no, '+86');
    }
    
    
    /**
     * 内容模版是否模板ID
     * @return bool
     */
    abstract public function isTemplateId() : bool;
    
    
    /**
     * 获取渠道名称
     * @return string
     */
    abstract public function getName() : string;
    
    
    /**
     * 获取主页
     * @return string
     */
    abstract public function getHomepage() : string;
    
    
    /**
     * 获取设置表单配置
     * @return array
     */
    abstract public function getSettingForm() : array;
    
    
    /**
     * 处理发送短信
     * @param string $no 手机号，如：+8613333333333 或 +8201011440
     * @param string $template 模板ID或模板内容，模板内容支持 `{变量名}` 变量
     * @param array  $vars 自定义变量键值对
     * @return mixed
     */
    abstract protected function handleSend(string $no, string $template, array $vars = []) : mixed;
    
    
    /**
     * 发送短信
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param string $template 模板ID或模板内容，模板内容支持 `{变量名}` 变量
     * @param array  $vars 自定义变量键值对
     * @return mixed
     */
    public function send(string $no, string $template, array $vars = []) : mixed
    {
        $no = trim($no);
        if (!$no) {
            throw new VerifyException('发送号码不能为空', 'no');
        }
        
        return $this->handleSend($this->coverNo($no), $template, $vars);
    }
    
    
    /**
     * 获取发送短信验证码账户类型
     * @return string
     */
    private function getCodeAccountType() : string
    {
        return Sms::getConfig('verify_code.account_type') ?: 'phone';
    }
    
    
    /**
     * 发送短信验证码
     * @param string $scene 验证码场景名称，依据 `config/sms.php` 中的 `template` 键，如：code
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param string $lang 指定语言标识，如 `zh-cn`
     * @return array{expire: int, repeat: int, length: int, style: int, code: string}
     * @throws Throwable
     */
    public function sendCode(string $scene, string $no, string $lang = '') : array
    {
        $lang     = $lang ?: Lang::getLangSet();
        $lang     = $lang == 'zh-cn' ? '' : '_' . $lang;
        $template = $this->config['template'][$scene . $lang] ?? '';
        if (!$template) {
            throw new RuntimeException(sprintf('未配置短信验证码模板: %s', $scene . $lang));
        }
        
        $no          = $this->coverNo($no);
        $accountType = $this->getCodeAccountType();
        $code        = VerifyCode::create($accountType, $no, $scene);
        try {
            $this->send($no, $template, ['code' => $code]);
        } catch (Throwable $e) {
            VerifyCode::clear($accountType, $no, $scene);
            
            throw $e;
        }
        
        return [
            'code'   => $code,
            'expire' => VerifyCode::getCodeExpire($accountType),
            'repeat' => VerifyCode::getCodeRepeat($accountType),
            'length' => VerifyCode::getCodeLength($accountType),
            'style'  => VerifyCode::getCodeStyle($accountType)
        ];
    }
    
    
    /**
     * 校验短信验证码
     * @param string $scene 验证码场景名称，依据 `config/sms.php` 中的 `template` 键，如：code
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param string $code 短信验证码
     * @param bool   $clear 验证完毕是否清理
     * @return void
     */
    public function checkCode(string $scene, string $no, string $code, bool $clear = true) : void
    {
        VerifyCode::check($this->getCodeAccountType(), $no, $code, $scene, $clear);
    }
    
    
    /**
     * 清理短信验证码
     * @param string $scene 验证码场景名称，依据 `config/sms.php` 中的 `template` 键，如：code
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @return void
     */
    public function clearCode(string $scene, string $no) : void
    {
        VerifyCode::clear($this->getCodeAccountType(), $no, $scene);
    }
}