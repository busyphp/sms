<?php

namespace BusyPHP\sms\app\controller;

use BusyPHP\app\admin\controller\AdminController;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use BusyPHP\exception\VerifyException;
use BusyPHP\helper\FilterHelper;
use BusyPHP\sms\facade\Sms;
use BusyPHP\sms\SmsConfig;
use RuntimeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\Response;

/**
 * 短信设置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/4 下午2:12 IndexController.php $
 */
class IndexController extends AdminController
{
    use SmsConfig;
    
    /**
     * 短信设置
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function setting()
    {
        $channels = $this->getSmsConfig('channels', []);
        $info     = SystemPlugin::init()->getSetting('busyphp/sms');
        if ($this->isPost()) {
            $action = $this->post('action/s', 'trim');
            
            // 设置参数
            if ($action === 'setting') {
                $type         = $this->post('type/s', 'trim');
                $channelList  = $this->post('channels/a');
                $templateList = $this->post('templates/a');
                $channel      = $channelList[$type] ?? [];
                $template     = $templateList[$type] ?? [];
                if (!$channel || !$template) {
                    throw new VerifyException('参数异常');
                }
                $info['channels'][$type]  = FilterHelper::trim($channel);
                $info['templates'][$type] = FilterHelper::trim($template);
                SystemPlugin::init()->setSetting('busyphp/sms', $info);
                
                $name = Sms::channel($type)->getName();
                $this->log()->record(self::LOG_DEFAULT, "设置【{$name}】参数配置");
                
                return $this->success('设置成功');
            }
            
            //
            // 启用渠道
            elseif ($action === 'default') {
                $value = $this->post('value/s', 'trim');
                if (!$value) {
                    throw new VerifyException('参数异常');
                }
                
                $info['default'] = $value;
                SystemPlugin::init()->setSetting('busyphp/sms', $info);
    
                $name = Sms::channel($value)->getName();
                $this->log()->record(self::LOG_DEFAULT, "启用【{$name}】短信渠道");
                
                return $this->success('设置成功');
            }
            
            throw new RuntimeException('未知类型');
        }
        
        $list = [];
        foreach ($channels as $key => $item) {
            $item['name']     = Sms::channel($key)->getName();
            $item['homepage'] = Sms::channel($key)->getHomepage();
            $item['manager']  = [];
            foreach (Sms::channel($key)->getManagerForms() as $formItem) {
                $item['manager'][$formItem->key] = $formItem;
            }
            
            foreach ($item['templates'] as $field => $value) {
                $value['type']             = $value['type'] ?? '';
                $value['name']             = $value['name'] ?? '';
                $value['vars']             = $value['vars'] ?? [];
                $item['templates'][$field] = $value;
            }
            
            $list[$key] = $item;
        }
        
        $this->assign('channels', $list);
        $this->assign('info', $info);
        
        return $this->display();
    }
    
    
    protected function display($template = '', $charset = 'utf-8', $contentType = '', $content = '')
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'plugins_sms' . DIRECTORY_SEPARATOR;
        if ($template) {
            $template = $dir . $template . '.html';
        } else {
            $template = $dir . $this->request->action() . '.html';
        }
        
        return parent::display($template, $charset, $contentType, $content);
    }
}