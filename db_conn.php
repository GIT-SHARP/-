<?php
// 数据库连接公共文件（所有模块引入此文件，避免重复代码）
$db_host = 'localhost';
$db_user = 'root'; // 测试环境可用root，正式环境建议创建ecommerce_user
$db_pass = '【填写你的root密码，如Ecommerce@123456】'; // 留白：修改为你的数据库密码
$db_name = 'ecommerce_db';

// 建立MySQL/MariaDB连接
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// 验证连接是否成功
if (!$conn) {
    die("数据库连接失败：" . mysqli_connect_error());
}

// 设置数据库字符集（防止中文乱码，必须配置）
mysqli_set_charset($conn, 'utf8mb4');
?>