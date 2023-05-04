<?php

namespace app\common\services;

use app\common\model\order\Order;
use app\common\model\User;
use app\common\model\user\Distribution;
use app\common\model\wallet\Account;
use app\common\model\wallet\Role;
use app\common\model\wallet\WalletFlow;
use app\common\services\wallet\WalletService;
use app\common\services\wallet\WithdrawService;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Log;

/**
 * 分销相关
 * Class DistributionServer
 * @package app\common\services
 */
class DistributionServer
{
    protected $userModel;
    protected $distributionModel;

    public function __construct()
    {
        $this->userModel         = new User();
        $this->distributionModel = new Distribution();
    }

    /**
     * 获取分销参数
     * @param int $userId 用户ID
     * @param float $orderTotal 订单总价
     * @return array
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     * @author wangjingyu 2021/9/17 15:24
     */
    public function getRecommend($userId, $orderTotal)
    {
        $id1 = $this->distributionModel->where(['status' => 1, 'junior_user_id' => $userId])->value('superior_user_id');
        $id2 = $this->distributionModel->where(['status' => 1, 'junior_user_id' => $id1])->value('superior_user_id');

        $direct   = $this->userModel->where(['id' => $id1])->field(['id', 'level'])->find();
        $indirect = $this->userModel->where(['id' => $id2])->field(['id', 'level'])->find();

        if ($direct) {
            $direct->rate  = (int)$direct->level === 2 ? config('site.commission_advanced') : config('site.commission_primary');
            $direct->rate  = bcmul($direct->rate, 0.01, 4);
            $direct->price = round(bcmul($direct->rate, $orderTotal, 4), 2);
        }
        if ($indirect) {
            $indirect->rate  = config('site.commission_indirect');
            $indirect->rate  = bcmul($indirect->rate, 0.01, 4);
            $indirect->price = round(bcmul($indirect->rate, $orderTotal, 4), 2);
        }
        Log::info('人员ID:' . $userId . ',价格：' . $orderTotal);
        Log::info('直推信息：' . json_encode($direct));
        Log::info('间推信息：' . json_encode($indirect));
        return ['direct' => $direct, 'indirect' => $indirect];
    }

    /**
     * 处理分销逻辑
     *
     * @param $grouponItems
     * @param $rules
     * @throws Exception
     */
    public function handleDistribution($grouponItems, $rules)
    {
        // 1. 计算分销金额
        $isLimitSuccess = $rules['is_limit_success'] ?? '';
        $teamSuccessNum = $rules['team_success_num'] ?? '';

        if (intval($isLimitSuccess) == 1) {
            $order = $grouponItems[0]['order'];
            $ratio = config('site.red_envelope_ratio') ?? 8; // 比例

            $errorNum = (int)$rules['team_num'] - (int)$rules['team_success_num'];
            Log::info('失败人数：' . $errorNum);

            $successAmount = (int)$teamSuccessNum * bcsub($order->order_amount, $order->cost_amount, 4);
            Log::info('1、成功金额：' . $successAmount);

            $errorAmount = bcmul($errorNum, $order->order_amount, 4);
            Log::info('2、失败金额：' . $errorAmount);

            $redAmount = bcmul($errorAmount, ($ratio / 100), 4); // 下单金额的8%;
            Log::info('3、红包支出金额：' . $redAmount . ', 红包福利比例：' . ($ratio / 100));

            $amount = bcsub($successAmount, $redAmount, 4);
            Log::info('4、利润金额：' . $amount);

        } else {
            $order          = $grouponItems[0]['order'];
            $teamSuccessNum = (int)$rules['team_num'];
            $successAmount  = $teamSuccessNum * bcsub($order->order_amount, $order->cost_amount, 4);
            Log::info('1、成功金额：' . $successAmount);
            $amount = $successAmount;
            Log::info('4、利润金额：' . $amount);
        }

        // 2. 处理分销
        foreach ($grouponItems as $grouponItem) {
            $order = $grouponItem->order;
            try {
                $this->grouponDistribution($order->user_id, $amount, $order);
                Log::log('订单：' . $order->order_no . '分销成功！');
            } catch (DataNotFoundException | ModelNotFoundException | DbException | Exception $e) {
                Log::log('订单：' . $order->order_no . '分销失败！原因：' . $e->getMessage());
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * 订单分销
     * @param $userId
     * @param $tradeAmount
     * @param $order
     * @return array|false
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws \think\Exception
     * @throws DataNotFoundException
     */
    public function grouponDistribution($userId, $tradeAmount, $order)
    {
        try {
            $walletService         = new WalletService();
            $DistributionServer    = new DistributionServer();
            $distributionAccountId = Account::ACCOUNT_ID_USER_VIRTUAL;
            $toPlatformAccountId   = Account::ACCOUNT_ID_PLATFORM_VIRTUAL;
            $platformUserId        = 0;
            // 获取分销数据
            $distribution = $DistributionServer->getRecommend($userId, $tradeAmount);
            // 直推
            if (!empty($distribution['direct'])) {
                $directPrice     = $distribution['direct']['price'];
                $directUserId    = $distribution['direct']['id'];
                $directUserLevel = $distribution['direct']['level_text'];
                Log::info('直推价格：' . $directPrice . ',直推人员ID:' . $directUserId . ',level:' . $directUserLevel);
                $directTradeInfo = [
                    'trade_title'   => '分销-直推奖励',
                    'memo'          => $directUserLevel . '直推奖励',
                    'trade_amount'  => $directPrice,
                    'business_type' => WalletFlow::BUSINESS_TYPE_UNFREEZE, // 分销直接到账
                    'order_type'    => Order::ORDER_TYPE_REALl
                ];
                // 1.保存钱包数据
                // 1.1 保存平台钱包数据
                $platformDirectWallet = $walletService->saveWallet('-' . $directPrice, $toPlatformAccountId, $platformUserId, Role::WALLET_ROLE_PLATFORM);
                // 1.2.保存用户账户钱包数据
                $userDirectWallet = $walletService->saveWallet($directPrice, $distributionAccountId, $directUserId, Role::WALLET_ROLE_USER);
                // 1.3. 保存钱包流水
                $walletService->saveWalletFlows($platformDirectWallet, $userDirectWallet, $directTradeInfo, $order);
            }
            // 间推
            if (!empty($distribution['indirect'])) {
                $indirectPrice  = $distribution['indirect']['price'];
                $indirectUserId = $distribution['indirect']['id'];
                Log::info('间推价格：' . $indirectPrice . ',人员ID:' . $indirectUserId);
                $indirectTradeInfo = [
                    'trade_title'   => '分销-间推奖励',
                    'memo'          => '间推奖励',
                    'trade_amount'  => $indirectPrice,
                    'business_type' => WalletFlow::BUSINESS_TYPE_UNFREEZE, // 分销直接到账
                    'order_type'    => Order::ORDER_TYPE_REALl
                ];
                // 1.保存钱包数据
                // 1.1 保存平台钱包数据
                $platformIndirectWallet = $walletService->saveWallet('-' . $indirectPrice, $toPlatformAccountId, $platformUserId, Role::WALLET_ROLE_PLATFORM);
                // 1.2.保存用户账户钱包数据
                $userIndirectWallet = $walletService->saveWallet($indirectPrice, $distributionAccountId, $indirectUserId, Role::WALLET_ROLE_USER);
                // 1.3. 保存钱包流水
                $walletService->saveWalletFlows($platformIndirectWallet, $userIndirectWallet, $indirectTradeInfo, $order);
            }
            // 系统抽成
            $systemPrice     = bcmul($order->order_amount, 0.3, 2);
            $systemUserId    = 2;  //hongweizhiyuan微信
            $systemTradeInfo = [
                'trade_title'   => '分销-系统抽成',
                'memo'          => '系统抽成',
                'trade_amount'  => $systemPrice,
                'business_type' => WalletFlow::BUSINESS_TYPE_UNFREEZE, // 分销直接到账
                'order_type'    => Order::ORDER_TYPE_REALl
            ];
            // 1.保存钱包数据
            // 1.1 保存平台钱包数据
            $platformSystemWallet = $walletService->saveWallet('-' . $systemPrice, $toPlatformAccountId, $platformUserId, Role::WALLET_ROLE_PLATFORM);
            // 1.2.保存用户账户钱包数据
            $userSystemWallet = $walletService->saveWallet($systemPrice, Account::ACCOUNT_ID_USER_WECHAT, $systemUserId, Role::WALLET_ROLE_USER);
            // 1.3. 保存钱包流水
            $walletFlow = $walletService->saveWalletFlows($platformSystemWallet, $userSystemWallet, $systemTradeInfo, $order);

            $user = (new User())->where(['id' => $systemUserId])->find();
            // 1.4 系统抽成转账到微信零钱
            $postData = [
                'partner_trade_no' => $walletFlow->trade_no, // 商户订单号，需保持唯一性
                'openid'           => $user->openid, // 商户appid下，某用户的openid
                'check_name'       => 'NO_CHECK', // NO_CHECK：不校验真实姓名  FORCE_CHECK：强校验真实姓名
                'amount'           => $systemPrice * 100, // 付款金额，单位为分
                'desc'             => '企业微信付款',
            ];

            $result = (new WithdrawService)->transferToWechat($postData);
            Log::info('系统抽成转账到微信零钱，返回数据：' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            Log::error('grouponDistribution error:' . json_encode($e));
        }

    }
}
