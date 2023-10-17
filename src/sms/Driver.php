<?php
declare(strict_types = 1);

namespace BusyPHP\sms;

use BusyPHP\exception\VerifyException;
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
     * @param string $content 模板ID或模板内容，模板内容支持 `{变量名}` 变量
     * @param array  $vars 自定义变量键值对
     * @return mixed
     * @throws Throwable
     */
    abstract protected function handle(string $no, string $content, array $vars = []) : mixed;
    
    
    /**
     * 发送短信
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param string $content 模板ID或模板内容，模板内容支持 `{变量名}` 变量
     * @param array  $vars 自定义变量键值对
     * @return mixed
     * @throws Throwable
     */
    public function send(string $no, string $content, array $vars = []) : mixed
    {
        $no = trim($no);
        if (!$no) {
            throw new VerifyException('发送号码不能为空', 'no');
        }
        
        return $this->handle($this->coverNo($no), $content, $vars);
    }
    
    
    /**
     * 通过模版ID发送短信
     * @param string $id 模板ID，依据 `config/sms.php` 中的 `template` 键，如：`login`
     * @param string $no 手机号，如：+8613333333333 或 13333333333
     * @param array  $vars 模板变量
     * @param string $lang 指定语言标识，如 `zh-cn`
     * @return mixed
     * @throws Throwable
     */
    public function sendTemplate(string $id, string $no, array $vars = [], string $lang = '') : mixed
    {
        $lang     = strtolower($lang ?: Lang::getLangSet());
        $lang     = $lang == 'zh-cn' ? '' : '_' . $lang;
        $key      = $id . $lang;
        $template = $this->config['template'][$key] ?? '';
        if (!$template) {
            throw new RuntimeException(sprintf('未配置短信模板: %s', $key));
        }
        
        return $this->send($no, $template, $vars);
    }
}