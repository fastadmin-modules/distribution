# 分销管理

## 快速使用
```
cd addons & 
git clone https://github.com/sxqibo/fastadmin-addon-distribution distribution & 
php think addon -a distribution -c package & 
rm -rf distribution

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
user_distribution   用户管理 - 分销表
user_invitation     用户管理 - 推广表

## 三：接口


## 四：依赖
依赖钱包模块儿
QrCode
OSS

## 五：相关图片


