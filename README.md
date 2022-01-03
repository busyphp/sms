短信模块
===============

> 支持短信验证码发送、验证，支持发送提醒短信、营销短信等，整合了主流的短信接口，如：腾讯云、阿里云等

## 安装方式

```shell script
composer require busyphp/sms
```

> 安装完成后可以通过后台管理 > 开发模式 > 插件管理进行 `安装/卸载`

## 配置 `config/busy-sms.php`

```php
<?php
return [
    // 默认短信通道
    'default'     => 'aliyun',
    
    // 配置短信通道
    'channels'    => [
        // 阿里云短信配置
        'aliyun'    => [
            'driver'        => 'aliyun',
            'config'        => [
                'access_key_id'     => '',
                'access_key_secret' => '',
                'region'            => '',
                'sign'              => '',
            ],
            'template_type' => 'id',
            'templates'     => [
                'login' => '' // 阿里云模板ID
            ]
        ],
        
        // 腾讯云短信配置
        'tencent'   => [
            'driver'        => 'tencent',
            'config'        => [
                'secret_id'   => '',
                'secret_key'  => '',
                'sdk_app_id'  => '',
                'app_key'     => '',
                'sign'        => '',
                'region'      => '',
                'sender_id'   => '',
                'extend_code' => '',
            ],
            'template_type' => 'id',
            'templates'     => [
                'login' => '' // 腾讯云模板ID
            ]
        ],
        
        // SUBMAIL赛邮云通信
        'mysubmail' => [
            'driver'        => 'mysubmail',
            'config'        => [
                'app_id'  => '',
                'app_key' => '',
                'sign'    => '函拓科技'
            ],
            'template_type' => '',
            'templates'     => [
                'login' => '您正在执行登录操作，验证码为{code}，10分钟内有效', // 登录验证码文案定义
            ]
        ]
    ],
    
    // 短信验证码配置
    'verify_code' => [
        // 账户类型
        'account_type' => 'phone'
    ],
    
    // 短信模板配置
    'templates'   => [
        'login' => [
            'name' => '登录验证码', // 后台管理表单名称
            'vars' => [
                '验证码' => 'code' // 变量名称
            ]
        ]
    ],
];
```

## 发送短信
```php
// 单发/批量发送相同的短信 - 自定义模板内容
\BusyPHP\sms\facade\Sms::send('手机号', '登录验证码为{code}', ['code' => 123456]);
\BusyPHP\sms\facade\Sms::send(['手机号1','手机号2'], '登录验证码为{code}', ['code' => 123456]);

// 单发/批量发送相同的短信 - 模板ID
\BusyPHP\sms\facade\Sms::send('手机号', 'TEMPLATE_ID1', ['code' => 123456]);
\BusyPHP\sms\facade\Sms::send(['手机号1','手机号2'], 'TEMPLATE_ID1', ['code' => 123456]);

// 批量发送 - 自定义模板内容
$data = new \BusyPHP\sms\contract\SmsBatchSendData();
$data->add('手机号1',['code' => 123456]);
$data->add('手机号2',['code' => 123456]);
$data->add('手机号3',['code' => 123456]);
$data->add('手机号4',['code' => 123456]);
\BusyPHP\sms\facade\Sms::batchSend($data, '登录验证码为{code}');

// 批量发送 - 模板ID
\BusyPHP\sms\facade\Sms::batchSend($data, 'TEMPLATE_ID1');

```

## 验证码发送/验证/清理

```php

// 发送
$code = \BusyPHP\sms\facade\SmsCode::send('手机号', 'login');

// 发送 - 定义验证码为字母数字混合
$code = \BusyPHP\sms\facade\SmsCode::send('手机号', 'login', \BusyPHP\verifycode\model\VerifyCode::SHAPE_LETTER_NUMBER);

// 发送 - 使用特定渠道
$code = \BusyPHP\sms\facade\SmsCode::send('手机号', 'login', null, 'aliyun');

// 验证
\BusyPHP\sms\facade\SmsCode::check('手机号', 'login', '验证码');

// 验证过程不清理短信
\BusyPHP\sms\facade\SmsCode::check('手机号', 'login', '验证码', false);

// 清理验证码
\BusyPHP\sms\facade\SmsCode::clear('手机号', 'login');
```