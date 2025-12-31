简易电商系统（PHP+MariaDB）- README
# 简易电商系统（PHP+MariaDB）- README
## 项目概述
这是一个基于 **PHP + MariaDB（兼容MySQL）** 开发的简易电商系统，采用原生PHP开发（无框架），实现了电商平台的核心闭环流程，适合PHP入门学习者、Web项目部署初学者进行学习和实验。
### 技术栈
•	后端：PHP 7.4+（mysqli扩展，预处理语句防SQL注入）
•	数据库：MariaDB 10.5+ / MySQL 5.7+（utf8mb4字符集，保证中文兼容）
•	前端：HTML5 + CSS3（原生样式，无前端框架，保证简洁易读）
•	服务器：Nginx 1.16+（推荐）/ Apache
### 核心功能
模块	具体功能
后台管理	管理员登录、商品添加、商品删除、商品列表查看
前台用户	用户注册、用户登录、退出登录、个人中心查看
商品模块	商品列表展示、商品详情查看、默认图片兜底
购物车模块	商品添加、数量修改、商品删除、总金额计算
订单模块	唯一订单号生成、订单创建、库存扣减、订单详情查看、购物车清空
## 环境要求
### 硬件环境
•	本地虚拟机：【填写本地虚拟机配置，如CentOS 7 1核2G】
•	云服务器：【填写云服务器配置，如阿里云ECS 1核2G 40G云盘】
•	本地主机：【填写本地主机配置，如Windows 10/11 + PHPStudy】
### 软件环境（必须满足）
1.	操作系统：CentOS 7+ / Ubuntu 18.04+ / Windows（需搭配PHPStudy等集成环境）
2.	PHP版本：7.4+（开启mysqli扩展、file_uploads扩展）
3.	数据库版本：MariaDB 10.5+ / MySQL 5.7+
4.	Web服务器：Nginx 1.16+ / Apache 2.4+
5.	其他：开启PHP会话（session）支持、目录读写权限
## 快速部署步骤
### 步骤1：环境搭建（前置准备）
#### 方案1：Linux服务器（LNMP环境搭建）
6.	安装Nginx：yum install -y nginx（CentOS）/ apt install -y nginx（Ubuntu）
7.	安装PHP及扩展：yum install -y php php-fpm php-mysqli php-common
8.	安装MariaDB：yum install -y mariadb mariadb-server
9.	启动并设置开机自启：
        # 启动服务
systemctl start nginx php-fpm mariadb
# 开机自启
systemctl enable nginx php-fpm mariadb
#### 方案2：Windows本地（集成环境搭建）
10.	下载并安装PHPStudy、XAMPP等集成环境（推荐PHPStudy）
11.	启用Nginx/Apache、PHP 7.4+、MariaDB/MySQL服务
12.	验证环境：访问集成环境默认首页，确认PHP、数据库连接正常
### 步骤2：项目源码部署
13.	下载/复制项目所有源码，放置到Web服务器根目录：
        
￮	Linux（Nginx）：/usr/share/nginx/html/
￮	Windows（PHPStudy）：WWW/电商系统/
14.	确认项目目录结构完整（如下），缺失目录手动创建：
        电商系统/
├── sql/                # 数据库初始化脚本
├── conn/               # 数据库连接配置
├── public/             # 公共资源（CSS、图片）
│   ├── css/
│   └── images/         # 商品图片目录（需赋予读写权限）
├── user/               # 用户模块
├── goods/              # 商品模块
├── cart/               # 购物车模块
├── order/              # 订单模块
└── admin/              # 后台管理模块
15.	配置目录权限（Linux环境）：
        # 赋予公共图片目录读写权限
chmod -R 775 /usr/share/nginx/html/public/images/
# 赋予项目目录所属用户为nginx
chown -R nginx:nginx /usr/share/nginx/html/
### 步骤3：数据库初始化
16.	登录MariaDB/MySQL（使用root用户）：
        mysql -u root -p
17.	创建电商数据库（utf8mb4字符集，保证中文无乱码）：
        CREATE DATABASE ecommerce_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
18.	导入数据库初始化脚本（修正版，无索引冲突）：
        # 退出数据库，执行导入命令（Linux环境）
mysql -u root -p ecommerce_db < /usr/share/nginx/html/sql/ecommerce_init.sql
￮	Windows环境：通过PHPStudy/phpMyAdmin导入sql/ecommerce_init.sql文件
19.	验证导入结果：登录数据库，确认ecommerce_db下包含user、goods、admin等6张表，且admin表存在默认管理员数据。
### 步骤4：核心配置修改
20.	编辑数据库连接配置文件 conn/db_conn.php：
        // 修改为你的数据库实际配置（重点修改密码）
$db_host = 'localhost';
$db_user = 'root';          // 测试环境可用root，正式环境建议创建专用用户
$db_pass = '【你的数据库root密码】';  // 填写你重置/设置的数据库密码
$db_name = 'ecommerce_db';
21.	保存配置文件，确保无语法错误（PHP语法错误会导致页面500报错）。
### 步骤5：项目访问与验证
22.	访问前台首页：http://你的服务器IP/goods/index.php（或http://localhost/goods/index.php（本地环境））
23.	访问后台登录页：http://你的服务器IP/admin/login.php
24.	验证核心功能：
       
￮	后台：使用默认账号admin、密码admin123456登录，尝试添加/删除商品
￮	前台：用户注册→登录→浏览商品→加入购物车→创建订单→查看个人中心订单
## 常见问题汇总
问题现象	核心原因	解决方案
ERROR 1061 (42000): Duplicate key name 'order_sn'	订单表order_sn字段UNIQUE约束与手动索引重复	导入修正版sql/ecommerce_init.sql脚本，删除重复索引
ERROR 1045 (28000): Access denied for user 'root'@'localhost'	数据库root密码错误/遗忘	跳过权限验证，重置MariaDB/MySQL root密码
管理员登录提示密码错误（输入正确）	admin表密码MD5值不匹配/数据库连接错误	1. 手动更新admin表密码为正确MD5值；2. 验证conn/db_conn.php配置
Access denied for user 'ecommerce_user'@'localhost'	ecommerce_user用户不存在/无权限	1. 改用root用户连接；2. 创建ecommerce_user并授予ecommerce_db权限
商品图片无法显示	默认图片缺失/目录权限不足	1. 在public/images/下创建default.jpg；2. 赋予目录775权限
页面500报错	PHP语法错误/扩展未开启/目录权限不足	1. 查看PHP错误日志；2. 开启mysqli扩展；3. 检查项目目录权限
## 注意事项
25.	安全提示：
       
￮	该项目为学习/实验用途，正式环境部署需优化安全配置（关闭PHP错误提示、限制文件上传大小、使用HTTPS、创建低权限数据库用户）
￮	密码仅采用MD5加密（入门级），正式环境建议使用password_hash()/password_verify()进行密码加密验证
￮	预处理语句已防SQL注入，请勿随意修改查询逻辑为直接拼接SQL
26.	功能留白补充：
        
￮	商品图片上传功能暂未完善，可在admin/goods_manage.php中补充文件上传逻辑
￮	订单支付功能为模拟提示，正式环境可对接支付宝/微信支付接口
￮	可扩展功能：用户密码找回、商品分类、订单状态更新、后台分页查询
27.	数据备份：定期备份ecommerce_db数据库，避免数据丢失：
        # 数据库备份命令（Linux）
mysqldump -u root -p ecommerce_db > ecommerce_db_backup_$(date +%Y%m%d).sql
## 项目目录结构详解（补充）
bash
├── sql/                # 数据库脚本目录
│   └── ecommerce_init.sql  # 数据库初始化（建表+默认数据）
├── conn/               # 公共配置目录
│   └── db_conn.php     # 数据库连接配置（全局复用）
├── public/             # 静态资源目录
│   ├── css/            # 统一样式表（全局复用，减少冗余）
│   └── images/         # 商品图片存储目录（默认图片+上传图片）
├── user/               # 前台用户模块
│   ├── register.php    # 用户注册
│   ├── login.php       # 用户登录
│   └── center.php      # 个人中心/订单查询
├── goods/              # 商品模块
│   ├── index.php       # 商品列表首页
│   └── detail.php      # 商品详情页
├── cart/               # 购物车模块
│   ├── cart.php        # 购物车主页（查看/修改/删除）
│   ├── add_cart.php    # 添加购物车接口
│   ├── del_cart.php    # 删除购物车接口
│   └── update_cart.php # 修改购物车数量接口
├── order/              # 订单模块
│   ├── create_order.php # 创建订单（事务保证数据一致性）
│   └── order_detail.php # 订单详情页
└── admin/              # 后台管理模块
    ├── login.php       # 管理员登录
    └── goods_manage.php # 商品管理（添加/删除/列表）
## 总结
该项目实现了电商系统的核心闭环流程，代码简洁易懂，无复杂框架依赖，适合PHP入门学习者理解动态网站的数据交互流程和业务逻辑设计。通过部署和调试该项目，可快速掌握LNMP环境搭建、PHP与数据库协同开发、常见Web项目问题排查等核心技能。
### 后续扩展方向
28.	前端优化：引入Bootstrap/Vue.js提升页面美观度和交互性
29.	后端优化：引入PHP框架（如ThinkPHP、Laravel）简化开发流程
30.	功能扩展：添加商品评论、收藏、物流查询、后台用户管理等功能
31.	部署优化：使用Docker容器化部署，简化环境搭建流程
