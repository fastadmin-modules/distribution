<?php

namespace app\api\controller\user;

use app\common\controller\Mini;
use Endroid\QrCode\Exception\InvalidPathException;
use OSS\Core\OssException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Request;

class Distribution extends Mini
{
    protected $noNeedRight = ['*'];

    /**
     * Goods模型对象
     */
    protected $distribution = null;
    protected $invitation   = null;
    protected $userModel    = null;
    protected $userId       = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $userInfo = $this->auth->getUser();
        $userInfo && $this->userId = $userInfo->id;

        $this->distribution = new \app\common\model\user\Distribution();
        $this->invitation   = new \app\common\model\user\Invitation();
        $this->userModel    = new \app\common\model\User();
    }

    /**
     * 获取我的分销
     * @author wangjingyu 2021/9/15 14:38
     */
    public function getDistributionInfo()
    {
        $data = [];
        try {
            $superiorId                 = $this->distribution->where(['junior_user_id' => $this->userId, 'status' => 1])->value('superior_user_id');
            $superiorData               = $this->userModel->where(['id' => $superiorId])->field(['id', 'nickname', 'mobile', 'level', 'is_valid'])->find();
            $direct                     = $this->distribution->where(['superior_user_id' => $this->userId, 'status' => 1])->column('junior_user_id');
            $data                       = $this->userModel->where(['id' => $this->userId])->field(['id', 'nickname', 'mobile', 'level', 'is_valid'])->find();
            $invitation                 = $this->invitation->where(['user_id' => $this->userId])->field('update_time', true)->find();
            $data['superior_name']      = $superiorData['nickname'] ?? '';
            $data['invitation_code']    = $invitation['code'] ?? '';
            $data['invitation_qr_code'] = $invitation['qr_code'] ?? '';
            $data['invitation_link']    = config('site.invitation_link') . '?code=' . $invitation['code'];
            $data['direct']             = count($direct);
            $data['indirect']           = $this->distribution->where(['superior_user_id' => ['in', $direct], 'status' => 1])->count();
            $data['rate']               = ((int)$data['level'] === 2 ? config('site.commission_advanced') : config('site.commission_primary')) . '%';

            // 我的业绩
            $walletFlow    = new \app\common\model\wallet\WalletFlow();
            $beginToday    = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endToday      = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $beginWeek     = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
            $endWeek       = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));
            $beginLastWeek = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
            $endLastWeek   = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));

            $where         = ['business_type' => \app\common\model\wallet\WalletFlow::BUSINESS_TYPE_DISTRIBUTION_UNFREEZE, 'trade_from_user_id' => 0, 'trade_from_user_type' => \app\common\model\wallet\Wallet::USER_TYPE_PLATFORM, 'trade_to_user_id' => $this->userId, 'trade_to_user_type' => \app\common\model\wallet\Wallet::USER_TYPE];
            $whereToday    = ['create_time' => ['BETWEEN', [$beginToday, $endToday]]];
            $whereThisWeek = ['create_time' => ['BETWEEN', [$beginWeek, $endWeek]]];
            $whereLastWeek = ['create_time' => ['BETWEEN', [$beginLastWeek, $endLastWeek]]];

            $achievement['today']      = sprintf("%.2f", $walletFlow->where($where)->where($whereToday)->sum('trade_amount'));
            $achievement['this_week']  = sprintf("%.2f", $walletFlow->where($where)->where($whereThisWeek)->sum('trade_amount'));
            $achievement['last_week']  = sprintf("%.2f", $walletFlow->where($where)->where($whereLastWeek)->sum('trade_amount'));
            $achievement['cumulative'] = sprintf("%.2f", $walletFlow->where($where)->sum('trade_amount'));
            $data['achievement']       = $achievement;

        } catch (DataNotFoundException $e) {
            $this->error('获取失败：' . $e->getMessage());
        } catch (ModelNotFoundException $e) {
            $this->error('获取失败：' . $e->getMessage());
        } catch (DbException $e) {
            $this->error('获取失败：' . $e->getMessage());
        } catch (Exception $e) {
            $this->error('获取失败：' . $e->getMessage());
        }
        $this->success('获取成功！', $data);
    }

    /**
     * 获取业绩明细列表
     * @author wangjingyu 2021/9/24 15:22
     * @throws DbException
     */
    public function getAchievementList()
    {
        $walletFlow = new \app\common\model\wallet\WalletFlow();

        $page  = $this->request->get('page') ?? 1;
        $limit = $this->request->get('limit') ?? 10;
        $sort  = $this->request->get('sort') ?? 'id';
        $order = $this->request->get('order') ?? 'desc';
        $where = ['business_type' => \app\common\model\wallet\WalletFlow::BUSINESS_TYPE_DISTRIBUTION_UNFREEZE, 'trade_from_user_id' => 0, 'trade_from_user_type' => \app\common\model\wallet\Wallet::USER_TYPE_PLATFORM, 'trade_to_user_id' => $this->userId, 'trade_to_user_type' => \app\common\model\wallet\Wallet::USER_TYPE];
        $list  = $walletFlow->where($where)->field(['id', 'trade_amount', 'trade_title', 'memo', 'create_time'])->order($sort, $order)->paginate($limit);
        foreach ($list as $row) {
            $row['create_time_text'] = date("Y-m-d H:i:s", $row['create_time']);
        }
        $this->success('获取成功！', ['page' => (int)$page, 'total' => $list->total(), 'list' => $list->items()]);
    }

    /**
     * 获取我的分销列表
     * @author wangjingyu 2021/9/15 15:28
     */
    public function getDistributionList()
    {
        $data    = [];
        $page    = $this->request->get('page') ?? 1;
        $limit   = $this->request->get('limit') ?? 10;
        $sort    = $this->request->get('sort') ?? 'id';
        $order   = $this->request->get('order') ?? 'desc';
        $type    = $this->request->get('type') ?? 1; //类型:1=直推,2=间推
        $status  = $this->request->get('status') ?? 1; //推广状态:1=推广成功,2=推广失败
        $search  = $this->request->get('search') ?? ''; //搜索用户名称或手机号
        $isValid = $this->request->get('is_valid') ?? ''; //转化状态:1=有效会员,2=未下单用户
        $where   = ['user_distribution.status' => $status];
        // 类型:1=直推,2=间推
        $ids = $this->distribution->where(['superior_user_id' => $this->userId, 'status' => $status])->column('junior_user_id');
        if ((int)$type === 2) {
            $ids = $this->distribution->where(['superior_user_id' => ['in', $ids], 'status' => $status])->column('junior_user_id');
        } else {
            $ids = $this->distribution->where(['superior_user_id' => $this->userId, 'status' => $status])->column('junior_user_id');
        }
        if (!empty($search) || !empty($isValid)) {
            $userWhere = ['id' => ['in', $ids]];
            // 搜索用户名称或手机号
            !empty($search) ? $userWhere['nickname|mobile'] = ['LIKE', '%' . $search . '%'] : true;
            // 是否消费过
            !empty($isValid) ? $userWhere['is_valid'] = (int)$isValid : true;
            $ids = $this->userModel->where($userWhere)->column('id');
        }
        $where['junior_user_id'] = ['in', $ids];

        try {
            $ids  = $this->distribution->where($where)->column('junior_user_id');
            $list = $this->distribution
                ->with(['junior', 'orderStatistics'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {
                $row->getRelation('junior')->visible(['id', 'nickname', 'avatar', 'mobile', 'level', 'level_text', 'is_valid']);
                $row['order_num']        = $row->orderStatistics->num;
                $row['order_total']      = !empty($row->orderStatistics->total) ? $row->orderStatistics->total : '0.00';
                $row['create_time_text'] = date("Y-m-d", $row['create_time']);
                $row->visible(['describe', 'order_num', 'order_total', 'create_time_text', 'junior']);
            }
            $data['page']    = (int)$page;
            $data['total']   = $list->total();
            $data['valid']   = $this->userModel->where(['id' => ['in', $ids], 'is_valid' => 1])->count();
            $data['invalid'] = $this->userModel->where(['id' => ['in', $ids], 'is_valid' => 2])->count();
            $data['list']    = $list->items();
        } catch (DbException $e) {
            $this->error('获取失败：' . $e->getMessage());
        } catch (Exception $e) {
            $this->error('获取失败：' . $e->getMessage());
        }

        $this->success('获取成功！', $data);
    }

    /**
     * 绑定下级分销
     * @author wangjingyu 2021/9/15 14:21
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function bindingDistribution()
    {
        $code           = $this->request->post('code');
        $open_id        = $this->request->post('open_id');
        $invitationData = $this->invitation->where(['code' => $code])->find();
        if (!empty($open_id)) {
            $userData = $this->userModel->where(['openid' => $open_id])->find();
            if ($userData) {
                $this->userId = $userData->id;
            } else {
                $this->userModel->isUpdate(false)->save(['openid' => $open_id]);
                $this->userId = $this->userModel->id;
            }
        }
        if ($invitationData) {
            $status = true;
            $data   = ['junior_user_id' => $this->userId, 'superior_user_id' => $invitationData['user_id'], 'status' => 1];
            $junior = $this->distribution->where(['junior_user_id' => $this->userId, 'status' => 1])->find();
            if ($junior && $status) {
                $status           = false;
                $data['describe'] = $junior['superior_user_id'] === $invitationData['user_id'] ? '绑定失败，已绑定该用户！' : '绑定失败，已绑定他人！';
            }
            if ((int)$invitationData['user_id'] === (int)$this->userId && $status) {
                $status           = false;
                $data['describe'] = '绑定失败，不能绑定自己！';
            }
            $junior = $this->distribution->where(['junior_user_id' => $invitationData['user_id'], 'superior_user_id' => $this->userId, 'status' => 1])->find();
            if ($junior && $status) {
                $status           = false;
                $data['describe'] = '绑定失败，不能绑定到自己的下级！';
            }
            if ($status === true) {
                $this->distribution->isUpdate(false)->save($data);
                $upgradeNum    = config('site.upgrade_num');
                $superiorCount = $this->distribution->where(['superior_user_id' => $invitationData['user_id'], 'status' => 1])->count();
                if ($superiorCount >= $upgradeNum) {
                    $superior = $this->userModel->where(['id' => $invitationData['user_id']])->field(['id', 'level'])->find();
                    if ((int)$superior->level === 1) {
                        $superior->level = 2;
                        $superior->isUpdate(true)->save();
                    }
                }
                $this->success('绑定成功！');
            } else {
                $data['status'] = 2;
                $this->distribution->isUpdate(false)->save($data);
                $this->error($data['describe']);
            }
        } else {
            $this->error('绑定失败，此码不存在！');
        }
    }

    /**
     * 批量生成推广码
     * @author wangjingyu 2021/9/15 16:16
     */
    public function generateQrCode()
    {
        $ids   = $this->invitation->column('user_id');
        $ids   = $this->userModel->where(['id' => ['not in', $ids]])->column('id');
        $Qr    = new \app\common\services\InvitationServer();
        $count = 0;
        foreach ($ids as $item) {
            try {
                $count += $Qr->saveQrCode($item);
            } catch (InvalidPathException $e) {
                $this->error('生成失败：' . $e->getMessage());
            } catch (OssException $e) {
                $this->error('生成失败：' . $e->getMessage());
            } catch (Exception $e) {
                $this->error('生成失败：' . $e->getMessage());
            }
        }
        $this->success('共生成' . $count . '个！');
    }
}
