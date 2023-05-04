# 分销管理

## 快速使用
```
cd addons && 
git clone https://github.com/sxqibo/fastadmin-addon-distribution distribution &&
cd .. &&
php think addon -a distribution -c package &&
rm -rf addons/distribution

安装时到 runtime/addons/distribution-1.0.0.zip
```

## 一：简要介绍
分销可以有多级，一般法定规定最多只能有三级。
每一个用户都有唯一的上级，不会出现多个上级。

#### 1）举例成功分销
每个用户注册成功时都会有一个分销码，我们举例：
"用户1"注册成功时会有一个分销的二维码，当"用户2"（用户2从来没注册过）扫"用户1"的二维码时且注册成功时。
那么"用户1"为上级，"用户2"为下级。

#### 2）举例失败分销
当"用户1"有分销码，"用户2"也有分销码时，这时"用户2"扫"用户1"时，就会显示"绑定失败，已绑定他人！"


## 二：数据表
* user_distribution   用户管理 - 分销表
* user_invitation     用户管理 - 推广表

## 三：依赖
* 钱包模块儿
* QrCode
* OSS

## 四：接口文档

[点击查看接口文档](https://console-docs.apipost.cn/preview/233045b91662bc3c/3d7604fc21649804)

## 五：小程序
目录下的 `weixin` 文件夹是小程序的使用目录！
在 `app.json` 中引入文件，下边的注释是文档中所用，实际要去掉
```
{
  "pages": [
    "pages/textDetail/textDetail",
    "pages/extension/extension", // 分销页面
    "pages/extensionList/extensionList", // 分销列表（直推人数，间推人数）
    "pages/extensionInfo/extensionInfo", // 我的分销业绩
  ],
  "tabBar": {
    "list": [
      {
        "pagePath": "pages/extension/extension",
        "iconPath": "static/imgs/推广_.png",
        "selectedIconPath": "static/imgs/推广.png",
        "text": "推广"
      }
    ]
  }
}
```

## 六：uniapp
待定

## 七：哪个项目用到分销
* 惠赚点
* 菜齐了
* 康旅荟
* 乐悠悠

## 八：其它模块儿怎样配合
钱包的model需要设置，位置在：`application/common/model/wallet/Account.php`
代码参考如下：

```
const  ACCOUNT_ID_PLATFORM_VIRTUAL = 1; // 平台虚拟账户
const  ACCOUNT_ID_USER_VIRTUAL     = 3; // 用户虚拟账户
```

## 九：其他模块儿怎样调用分销模块儿
一般来说，本方法已经足够使用，但如果有特殊情况时，其他方法可以参考 `DistributionServer.php` 方法进行调用本模块儿，其实就是钱包分账的方式，大致方法可参考如下：
```
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
```

## 九：相关图片
待定
