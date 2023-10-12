<?php
declare(strict_types = 1);

namespace BusyPHP\sms;

use BusyPHP\app\admin\model\system\menu\SystemMenu;
use BusyPHP\sms\app\controller\SettingController;

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
        SystemMenu::registerAnnotation(SettingController::class);
    }
}
