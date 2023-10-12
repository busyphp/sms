短信模块
===============

> 支持短信验证码发送、验证，支持发送提醒短信、营销短信等，整合了主流的短信接口，如：腾讯云、阿里云等

## 安装方式

```shell script
composer require busyphp/sms
```

> 安装完成后可以通过后台管理 > 开发模式 > 插件管理进行 `安装`

> 配置 `config/sms.php`

```php
<?php
return [
    // 默认短信通道
    'default'     => 'aliyun',
    
    // 配置短信通道
    'drivers'     => [
        // 阿里云短信配置
        'aliyun'    => [
            'type'              => 'aliyun',
            
            // 阿里云短信参数
            'access_key_id'     => '',
            'access_key_secret' => '',
            'region'            => '',
            'sign'              => '',
            
            // 模版ID映射
            'template'          => [
                'login' => ''
            ]
        ],
        
        // 腾讯云短信配置
        'tencent'   => [
            'type'        => 'tencent',
            
            // 腾讯云短信参数
            'secret_id'   => '',
            'secret_key'  => '',
            'sdk_app_id'  => '',
            'app_key'     => '',
            'sign'        => '',
            'region'      => '',
            'sender_id'   => '',
            'extend_code' => '',
            
            // 模版ID映射
            'template'    => [
                'login' => ''
            ]
        ],
        
        // SUBMAIL赛邮云通信
        'mysubmail' => [
            'type'     => 'mysubmail',
            
            // SUBMAIL赛邮云通信参数
            'app_id'   => '',
            'app_key'  => '',
            'sign'     => '',
            
            // 模版内容映射
            'template' => [
                'login' => '您正在执行登录操作，验证码为{code}，10分钟内有效'
            ]
        ]
    ],
    
    // busyphp/verify-code插件配置
    'verify_code' => [
        // 设置发送短信验证码的账号类型
        'account_type' => 'phone'
    ],
    
    // 短信模板配置
    'template'    => [
        // 场景名称 => 配置
        'login' => [
            // 表单名称
            'name' => '登录验证码',
            // 支持变量
            'vars' => [
                'code' => '验证码'
            ]
        ]
    ],
    
    // 多语言配置，默认为简体中文，故不需要添加 `zh-cn`
    'lang'        => [
    ]
];
```

## 发送短信
```php
// 发送短信 - 自定义模板内容
\BusyPHP\facade\Sms::driver()->send('手机号', '登录验证码为{code}', ['code' => 123456]);

// 发送短信 - 模板ID
\BusyPHP\facade\Sms::driver()->send('手机号', 'TEMPLATE_ID1', ['code' => 123456]);
```

## 验证码发送/验证/清理

```php

// 发送
$code = \BusyPHP\facade\Sms::driver()->sendCode('login', '手机号');

// 发送 - 使用特定渠道
$code = \BusyPHP\facade\Sms::driver('aliyun')->sendCode('login', '手机号');

// 验证
\BusyPHP\facade\Sms::driver()->checkCode('login', '手机号', '验证码');

// 清理验证码
\BusyPHP\facade\Sms::driver()->clearCode('login', '手机号');
```