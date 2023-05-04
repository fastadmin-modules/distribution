<?php

namespace app\admin\command;

use app\common\model\User;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class HandleUserInvitation extends Command
{
    protected function configure()
    {
        $this->setName('handle:user_invitation')
            ->setDescription('操作用户的邀请码和二维码');
    }

    protected function execute(Input $input, Output $output)
    {
        // 清空数据
        Db::query('truncate table fzw_user_invitation');

        // 查询用户信息
        $userData = (new User())->select();

        // 遍历用户保存信息
        foreach ($userData as $k => $v) {

            (new \app\common\services\InvitationServer())->saveQrCode($v->id);
            $output->writeln("已操作用户ID：".$v->id);
        }

    }
}
