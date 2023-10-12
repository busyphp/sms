<?php
declare(strict_types = 1);

namespace BusyPHP\sms\app\controller;

use BusyPHP\app\admin\annotation\MenuNode;
use BusyPHP\app\admin\annotation\MenuRoute;
use BusyPHP\app\admin\component\js\driver\Table;
use BusyPHP\app\admin\controller\AdminController;
use BusyPHP\app\admin\model\system\config\SystemConfig;
use BusyPHP\app\admin\model\system\config\SystemConfigField;
use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\facade\Sms;
use BusyPHP\helper\FilterHelper;
use stdClass;
use think\Response;
use Throwable;

/**
 * 短信参数管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2023 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2023/10/11 17:43 SettingController.php $
 */
#[MenuRoute(path: 'plugin_sms', class: true)]
class SettingController extends AdminController
{
    protected function display($template = '', $charset = 'utf-8', $contentType = '', $content = '') : Response
    {
        $this->app->config->set([
            'view_path'   => __DIR__ . '/../view/',
            'view_depr'   => DIRECTORY_SEPARATOR,
            'view_suffix' => 'html',
            'auto_rule'   => 1
        ], 'view');
        
        return parent::display($template, $charset, $contentType, $content);
    }
    
    
    /**
     * @return Response
     * @throws Throwable
     */
    #[MenuNode(menu: false, name: '短信设置', parent: 'system_manager/index', sort: 201)]
    public function index() : Response
    {
        if ($table = Table::initIfRequest()) {
            $map = Sms::getConfig('drivers', []);
            if (!is_array($map)) {
                $map = [];
            }
            
            $list = [];
            foreach ($map as $id => $item) {
                $driver = Sms::driver($id);
                $list[] = [
                    'id'         => $id,
                    'homepage'   => $driver->getHomepage(),
                    'name'       => $driver->getName(),
                    'is_default' => Sms::getDefaultDriver() == $id
                ];
            }
            
            return $table->list($list)->response();
        }
        
        $this->assign('nav', SystemMenu::init()->getChildList('system_manager/index', true, true));
        
        return $this->display();
    }
    
    
    /**
     * @throws Throwable
     */
    #[MenuNode(menu: false, name: '配置短信参数', parent: '/index')]
    public function setting() : Response
    {
        $id         = $this->param('id/s', 'trim');
        $driver     = Sms::driver($id);
        $settingKey = Sms::getSettingKey($id);
        
        if ($this->isPost()) {
            $data = SystemConfigField::init();
            $data->setSystem(true);
            $data->setName(sprintf('短信渠道商配置 - %s', $id));
            $data->setContent(FilterHelper::trim($this->post('content/a')));
            SystemConfig::init()->setting($settingKey, $data);
            
            $this->log()->record(self::LOG_UPDATE, '配置短信参数');
            
            return $this->success('配置成功');
        }
        
        $content             = SystemConfig::instance()->getSettingData($settingKey);
        $content['template'] = $content['template'] ?? new stdClass();
        
        $this->assign('id', $id);
        $this->assign('content', $content);
        $this->assign('form', $driver->getSettingForm());
        $this->assign('name', $driver->getName());
        $this->assign('template_id', $driver->isTemplateId());
        $this->assign('template_config', Sms::getConfig('template'));
        
        // 多语言
        $langList = [['name' => '简体中文', 'lang' => '', 'suffix' => '']];
        foreach (Sms::getConfig('lang') ?: [] as $lang => $name) {
            if ($lang == 'zh-cn') {
                continue;
            }
            $langList[] = ['name' => $name, 'lang' => $lang, 'suffix' => '_' . $lang];
        }
        $this->assign('lang_list', $langList);
        
        return $this->display();
    }
    
    
    /**
     * @throws Throwable
     */
    #[MenuNode(menu: false, name: '设为默认通道', parent: '/index')]
    public function set_default() : Response
    {
        $id   = $this->param('id/s', 'trim');
        $data = SystemConfigField::init();
        $data->setSystem(true);
        $data->setName('短信默认通道配置');
        $data->setContent(['default' => $id]);
        SystemConfig::init()->setting(Sms::getDefaultSettingKey(), $data);
        $this->log()->record(self::LOG_UPDATE, '设置默认短信通道');
        
        return $this->success('设置成功');
    }
}