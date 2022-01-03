<?php

namespace BusyPHP\sms\contract;

/**
 * 批量发送短信参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/3 下午7:20 SmsBatchSendData.php $
 */
class SmsBatchSendData
{
    /**
     * @var array
     */
    private $list = [];
    
    
    /**
     * 快速实例化
     * @return static
     */
    public static function init() : SmsBatchSendData
    {
        return new static();
    }
    
    
    /**
     * 添加一组发送数据
     * @param string $phone 手机号
     * @param array  $vars 键值对变量
     */
    public function add(string $phone, array $vars)
    {
        $this->list[] = [
            'phone' => $phone,
            'vars'  => $vars
        ];
    }
    
    
    /**
     * 获取数据
     * @return array
     */
    public function getList() : array
    {
        return $this->list;
    }
}