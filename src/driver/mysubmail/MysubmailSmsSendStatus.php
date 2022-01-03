<?php

namespace BusyPHP\sms\driver\mysubmail;

use BusyPHP\model\ArrayOption;

/**
 * 赛邮云短信发送返回结构
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/1 下午5:53 MysubmailSmsSendStatus.php $
 * @property string $send_id 消息ID
 * @property float  $fee 消息计费
 * @property float  $account_balance 账号余额
 * @property string $sms_credits
 * @property string $transactional_sms_credits
 * @property string $status 状态 success 为成功，error为失败
 * @property int    $code 错误码，参考：https://www.mysubmail.com/documents/rK2yh3
 * @property string $msg 错误消息
 */
class MysubmailSmsSendStatus extends ArrayOption
{
    /**
     * 是否发送成功
     * @return bool
     */
    public function status() : bool
    {
        return $this->status === 'success';
    }
}