<?php
// 用户个人中心：展示我的订单、退出登录
session_start();
require_once '../conn/db_conn.php';

$msg = '';
$user_orders = [];

// 未登录，跳转登录页
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php?msg=请先登录再访问个人中心");
    exit();
}

// 处理退出登录
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // 销毁Session
    session_unset();
    session_destroy();
    header("Location: login.php?msg=已成功退出登录");
    exit();
}

// 查询当前用户的所有订单（关联订单主表和商品明细表）
$user_id = $_SESSION['user_id'];
$sql = "SELECT oi.id, oi.order_sn, oi.order_amount, oi.order_status, oi.create_time, 
        GROUP_CONCAT(og.goods_name, '(', og.goods_num, '件)') AS goods_list
        FROM `order_info` oi
        LEFT JOIN `order_goods` og ON oi.id = og.order_id
        WHERE oi.user_id = ?
        GROUP BY oi.id
        ORDER BY oi.create_time DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    // 格式化时间戳
    $row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
    // 格式化订单状态
    $row['order_status_text'] = $row['order_status'] == 1 ? '已支付' : '未支付';
    $user_orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>个人中心 - 电商系统</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h2>电商系统</h2>
            <div>
                <a href="../goods/index.php">首页</a>
                <a href="../cart/cart.php">我的购物车</a>
                <span class="user-info">欢迎，<?php echo $_SESSION['username']; ?></span>
                <a href="center.php?action=logout" style="color: #f0f9ff;">退出登录</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h3>我的订单</h3>
        <div class="msg <?php echo !empty($msg) ? 'msg-error' : ''; ?>"><?php echo $msg; ?></div>

        <?php if (empty($user_orders)): ?>
            <div style="text-align: center; margin: 50px 0; color: #666;">
                暂无订单，快去<a href="../goods/index.php" style="color: #4285F4;">首页</a>购物吧！
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>订单编号</th>
                        <th>订单金额</th>
                        <th>订单商品</th>
                        <th>订单状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_orders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_sn']; ?></td>
                            <td>¥<?php echo number_format($order['order_amount'], 2); ?></td>
                            <td><?php echo $order['goods_list']; ?></td>
                            <td><?php echo $order['order_status_text']; ?></td>
                            <td><?php echo $order['create_time']; ?></td>
                            <td>
                                <a href="../order/order_detail.php?order_id=<?php echo $order['id']; ?>" class="btn">查看详情</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>