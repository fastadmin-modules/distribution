<?php

use think\migration\Migrator;
use think\migration\db\Column;

class UserDistributionTable extends Migrator
{
    public function change()
    {
        $table = $this->table('user_distribution', ['collation' => 'utf8mb4_general_ci', 'comment' => '用户管理 - 分销表']);
        $table->addColumn(Column::integer('superior_user_id')->setLimit(11)->setDefault('')->setComment('上级用户ID'))
            ->addColumn(Column::integer('junior_user_id')->setLimit(11)->setDefault('')->setComment('下级用户ID'))
            ->addColumn(Column::enum('status', [1, 2])->setDefault(1)->setComment('推广状态:1=推广成功,2=推广失败'))
            ->addColumn(Column::string('describe', 255)->setDefault('')->setComment('失败原因描述'))
            // 时间
            ->addColumn(Column::integer('create_time')->setComment('创建时间'))
            ->addColumn(Column::integer('update_time')->setComment('修改时间'))
            ->create();


        $table = $this->table('user_invitation', ['collation' => 'utf8mb4_general_ci', 'comment' => '用户管理 - 推广表']);
        $table->addColumn(Column::integer('user_id')->setLimit(11)->setDefault('')->setComment('用户ID'))
            ->addColumn(Column::string('code', 100)->setDefault('')->setComment('推广码'))
            ->addColumn(Column::string('qr_code', 200)->setDefault('')->setComment('推广二维码'))
            // 时间
            ->addColumn(Column::integer('create_time')->setComment('创建时间'))
            ->addColumn(Column::integer('update_time')->setComment('修改时间'))
            ->create();

    }
}
