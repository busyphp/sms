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