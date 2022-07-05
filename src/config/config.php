<?php
/**
 * 短信配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午3:30 busy-sms.php $
 */

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
            'international' => false,
            'template_type' => 'id',
            'templates'     => [
                'login' => ''
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
            'international' => false,
            'template_type' => 'id',
            'templates'     => [
                'login' => ''
            ]
        ],
        
        // SUBMAIL赛邮云通信
        'mysubmail' => [
            'driver'        => 'mysubmail',
            'config'        => [
                'app_id'  => '',
                'app_key' => '',
                'sign'    => ''
            ],
            'international' => false,
            'template_type' => '',
            'templates'     => [
                'login' => '您正在执行登录操作，验证码为{code}，10分钟内有效'
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
            'name' => '登录验证码',
            'vars' => [
                '验证码' => 'code'
            ]
        ]
    ],
];