<?php

namespace app\common\model\user;

use app\common\model\order\Order;
use app\common\model\order\OrderItem;
use think\Model;


class Distribution extends Model
{
    // 表名
    protected $name = 'user_distribution';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
    ];

    public function getStatusList()
    {
        return ['1' => '推广成功', '2' => '推广失败'];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list  = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setCreateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUpdateTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function superior()
    {
        return $this->belongsTo('app\common\model\User', 'superior_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function junior()
    {
        return $this->belongsTo('app\common\model\User', 'junior_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function orderStatistics()
    {
        return $this->belongsTo('app\common\model\order\Order', 'junior_user_id', 'user_id')
            ->field(['count(id) num', 'sum(order_amount) total', 'user_id', 'pay_type'])
            ->whereExists(function ($query) {
                $orderItemTableName = (new OrderItem())->getQuery()->getTable();
                $orderTableName     = (new Order())->getQuery()->getTable();
                $query->table($orderItemTableName)
                    ->where('order_id=' . $orderTableName . '.id')
                    ->where('refund_status', OrderItem::REFUND_STATUS_NO_REFUND);
            });
    }
}
