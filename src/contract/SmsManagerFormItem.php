<?php

namespace BusyPHP\sms\contract;

use BusyPHP\model\ObjectOption;

/**
 * 管理表单单项规定
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/3 下午4:20 SmsManagerFormItem.php $
 */
class SmsManagerFormItem extends ObjectOption
{
    /**
     * @var string
     */
    public $key;
    
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var string
     */
    public $desc;
    
    /**
     * @var bool
     */
    public $must;
    
    /**
     * 选项
     * @var string
     */
    public $options;
    
    /**
     * 表单类型
     * @var string
     */
    public $type;
    
    
    /**
     * ManagerFormItem constructor.
     * @param string $key 字段名称
     * @param string $name 表单名
     * @param string $desc 描述
     * @param bool   $must 必填
     */
    public function __construct(string $key, string $name, string $desc, bool $must = true)
    {
        $this->key  = $key;
        $this->name = $name;
        $this->desc = $desc;
        $this->must = $must;
    }
    
    
    /**
     * 设置选项
     * @param array $options
     */
    public function setSelect(array $options)
    {
        $this->type    = 'select';
        $this->options = $options;
    }
}