<?php

namespace BusyPHP\sms\contract;

/**
 * 短信接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午5:33 SmsInterface.php $
 */
interface SmsInterface
{
    /**
     * 获取渠道名称
     * @return string
     */
    public function getName() : string;
    
    
    /**
     * 获取主页
     * @return string
     */
    public function getHomepage() : string;
    
    
    /**
     * 获取管理表单
     * @return SmsManagerFormItem[]
     */
    public function getManagerForms() : array;
    
    
    /**
     * 单发/批量发送相同内容的短信
     * @param string|string[] $phone 手机号，如：+8613333333333 或 [13333333333, +8613333333333]
     * @param string          $template 魔板ID或魔板内容，魔板内容支持 `{变量名}` 变量
     * @param array           $vars 自定义变量键值对
     * @param string          $attach 自定义附加数据，server会原样返回
     * @return mixed
     */
    public function send($phone, string $template, array $vars = [], string $attach = '');
    
    
    /**
     * 批量发送不同内容的短信
     * @param SmsBatchSendData $data 数据对象
     * @param string           $template 魔板ID或魔板内容，魔板内容支持 `{变量名}` 变量
     * @param string           $attach 自定义附加数据，server会原样返回
     * @return mixed
     */
    public function batchSend(SmsBatchSendData $data, string $template, string $attach = '');
}