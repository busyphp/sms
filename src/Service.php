<?php

namespace BusyPHP\sms;

use BusyPHP\Service as BaseService;
use BusyPHP\sms\app\controller\IndexController;
use think\Route;

/**
 * 服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/3 下午9:14 Service.php $
 */
class Service extends \think\Service
{
    public function boot()
    {
        $this->registerRoutes(function(Route $route) {
            $actionPattern = '<' . BaseService::ROUTE_VAR_ACTION . '>';
            
            // 后台路由
            if ($this->app->http->getName() === 'admin') {
                $route->rule("plugins_sms/{$actionPattern}", IndexController::class . "@{$actionPattern}")->append([
                    BaseService::ROUTE_VAR_TYPE    => 'plugin',
                    BaseService::ROUTE_VAR_CONTROL => 'plugins_sms',
                ]);
            }
        });
    }
}
