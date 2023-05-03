-- ----------------------------
-- 用户管理 - 分销
-- ----------------------------
CREATE TABLE `__PREFIX__user_distribution`
(
    `id`               int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ' ID',
    `superior_user_id` int(11)          NOT NULL COMMENT '上级用户ID',
    `junior_user_id`   int(11)          NOT NULL COMMENT '下级用户ID',
    `status`           enum ('1','2')   NOT NULL DEFAULT '1' COMMENT '推广状态:1=推广成功,2=推广失败',
    `describe`         varchar(255)     NOT NULL DEFAULT '' COMMENT '失败原因描述',
    `create_time`      int(10)          NOT NULL COMMENT '创建时间',
    `update_time`      int(10)          NOT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 24
  DEFAULT CHARSET = utf8mb4 COMMENT ='用户管理 - 分销表';

-- ----------------------------
-- 用户管理 - 推广表
-- ----------------------------
CREATE TABLE `__PREFIX__user_invitation`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`     int(11)          NOT NULL COMMENT '用户ID',
    `code`        varchar(100)     NOT NULL DEFAULT '' COMMENT '推广码',
    `qr_code`     varchar(200)     NOT NULL COMMENT '推广二维码',
    `create_time` int(10)                   DEFAULT NULL COMMENT '创建时间',
    `update_time` int(10)                   DEFAULT NULL COMMENT '修改时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 120
  DEFAULT CHARSET = utf8mb4 COMMENT ='用户管理 - 推广表';


-- ----------------------------
-- 用户分销记录
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user_distribution` (`id`, `superior_user_id`, `junior_user_id`, `status`, `describe`,
                                           `create_time`,
                                           `update_time`)
VALUES (1, 12, 20, '1', '', 1631959447, 1631959447);
INSERT INTO `__PREFIX__user_distribution` (`id`, `superior_user_id`, `junior_user_id`, `status`, `describe`,
                                           `create_time`,
                                           `update_time`)
VALUES (2, 3, 10, '2', '绑定失败，已绑定他人！', 1635297144, 1635297144);

-- ----------------------------
-- 用户推广记录
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__user_invitation` (`id`, `user_id`, `code`, `qr_code`, `create_time`, `update_time`)
VALUES (1, 1, 'TXQDEjft', 'https://huizhuandian.oss-cn-hangzhou.aliyuncs.com/code_imgs/20210915162130563.png',
        1631694091, 1631694091);
INSERT INTO `__PREFIX__user_invitation` (`id`, `user_id`, `code`, `qr_code`, `create_time`, `update_time`)
VALUES (2, 2, 'gWBFyPN0', 'https://huizhuandian.oss-cn-hangzhou.aliyuncs.com/code_imgs/20210915162132654.png',
        1631694092, 1631694092);
