-- 电商系统数据库初始化脚本
-- 使用ecommerce_db数据库
USE ecommerce_db;

-- 1. 用户表（user）- 前台用户注册/登录
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) NOT NULL UNIQUE COMMENT '用户名（唯一）',
  `password` varchar(32) NOT NULL COMMENT '密码（MD5加密）',
  `email` varchar(100) DEFAULT NULL COMMENT '用户邮箱',
  `create_time` int(10) NOT NULL COMMENT '注册时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户信息表';

-- 2. 商品表（goods）- 商品信息存储
DROP TABLE IF EXISTS `goods`;
CREATE TABLE IF NOT EXISTS `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名称',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `goods_stock` int(11) NOT NULL DEFAULT 0 COMMENT '商品库存',
  `goods_img` varchar(255) DEFAULT NULL COMMENT '商品图片路径（对应public/images）',
  `goods_desc` text DEFAULT NULL COMMENT '商品描述',
  `create_time` int(10) NOT NULL COMMENT '添加时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品信息表';

-- 3. 购物车表（cart）- 用户购物车数据
DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '购物车ID',
  `user_id` int(11) NOT NULL COMMENT '所属用户ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `goods_num` int(11) NOT NULL DEFAULT 1 COMMENT '商品数量',
  `create_time` int(10) NOT NULL COMMENT '加入购物车时间戳',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户购物车表';

-- 4. 订单主表（order_info）- 订单核心信息（修正：删除重复的 order_sn 索引）
DROP TABLE IF EXISTS `order_info`;
CREATE TABLE IF NOT EXISTS `order_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_sn` varchar(64) NOT NULL UNIQUE COMMENT '唯一订单编号', -- UNIQUE 约束自动创建唯一索引，无需额外添加
  `user_id` int(11) NOT NULL COMMENT '下单用户ID',
  `order_amount` decimal(10,2) NOT NULL COMMENT '订单总金额',
  `order_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单状态（0-未支付，1-已支付）',
  `create_time` int(10) NOT NULL COMMENT '下单时间戳',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) -- 仅保留用户ID索引，删除重复的 order_sn 索引
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单主表';

-- 5. 订单商品表（order_goods）- 订单商品明细（下单快照）
DROP TABLE IF EXISTS `order_goods`;
CREATE TABLE IF NOT EXISTS `order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单商品ID',
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `goods_id` int(11) NOT NULL COMMENT '商品ID',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名称（下单快照）',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格（下单快照）',
  `goods_num` int(11) NOT NULL COMMENT '商品数量',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单商品明细表';

-- 6. 管理员表（admin）- 后台管理员（简易版，可直接使用密码验证）
DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `admin_name` varchar(50) NOT NULL UNIQUE COMMENT '管理员账号',
  `password` varchar(32) NOT NULL COMMENT '管理员密码（MD5加密）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员信息表';

-- 插入默认管理员（账号：admin，密码：admin123456，MD5加密值：e10adc3949ba59abbe56e057f20f883e）
INSERT INTO `admin` (`admin_name`, `password`) VALUES ('admin', 'e10adc3949ba59abbe56e057f20f883e');