<?php
// 订单详情页：展示订单主信息和商品明细
session_start();
require_once '../conn/db_conn.php';

$msg = '';
$order = [];
$order_goods_list = [];

// 未登录，跳转登录页
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../user/login.php?msg=请先登录再查看订单详情");
    exit();
}

// 验证订单ID
if (empty($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    $msg = '无效的订单ID！';
} else {
    $user_id = $_SESSION['user_id'];
    $order_id = intval($_GET['order_id']);

    // 1. 查询订单主信息（验证是否属于当前用户）
    $order_sql = "SELECT id, order_sn, order_amount, order_status, create_time FROM `order_info` WHERE id = ? AND user_id = ? LIMIT 1";
    $order_stmt = mysqli_prepare($conn, $order_sql);
    mysqli_stmt_bind_param($order_stmt, 'ii', $order_id, $user_id);
    mysqli_stmt_execute($order_stmt);
    $order_result = mysqli_stmt_get_result($order_stmt);
    $order = mysqli_fetch_assoc($order_result);

    if (empty($order)) {
        $msg = '该订单不存在或不属于你！';
    } else {
        // 格式化订单信息
        $order['order_amount_format'] = number_format($order['order_amount'], 2);
        $order['create_time_format'] = date('Y-m-d H:i:s', $order['create_time']);
        $order['order_status_text'] = $order['order_status'] == 1 ? '已支付' : '未支付';

        // 2. 查询订单商品明细
        $goods_sql = "SELECT goods_name, goods_price, goods_num FROM `order_goods` WHERE order_id = ? ORDER BY id ASC";
        $goods_stmt = mysqli_prepare($conn, $goods_sql);
        mysqli_stmt_bind_param($goods_stmt, 'i', $order_id);
        mysqli_stmt_execute($goods_stmt);
        $goods_result = mysqli_stmt_get_result($goods_stmt);

        while ($row = mysqli_fetch_assoc($goods_result)) {
            $row['goods_price_format'] = number_format($row['goods_price'], 2);
            $row['sub_total'] = $row['goods_price'] * $row['goods_num'];
            $row['sub_total_format'] = number_format($row['sub_total'], 2);
            $order_goods_list[] = $row;
        }
    }
}

// 接收跳转提示信息
if (!empty($_GET['msg']) && empty($msg)) {
    $msg = $_GET['msg'];
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>订单详情 - 电商系统</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h2>电商系统</h2>
            <div>
                <a href="../goods/index.php">首页</a>
                <a href="../user/center.php">个人中心</a>
                <span class="user-info">欢迎，<?php echo $_SESSION['username']; ?></span>
            </div>
        </div>
    </div>

    <div class="container">
        <h3>订单详情</h3>
        <div class="msg <?php echo strpos($msg, '失败') !== false ? 'msg-error' : 'msg-success'; ?>"><?php echo $msg; ?></div>

        <?php if (!empty($msg) && strpos($msg, '失败') !== false): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../cart/cart.php" class="btn">返回购物车</a>
            </div>
        <?php elseif (empty($order)): ?>
            <div style="text-align: center; margin: 50px 0; color: #666;">
                暂无该订单信息！
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../user/center.php" class="btn">返回我的订单</a>
            </div>
        <?php else: ?>
            <div style="border: 1px solid #eee; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h4>订单基本信息</h4>
                <table style="width: 100%; margin-top: 10px;">
                    <tr>
                        <td style="width: 150px; font-weight: bold;">订单编号：</td>
                        <td><?php echo $order['order_sn']; ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">订单金额：</td>
                        <td>¥<?php echo $order['order_amount_format']; ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">订单状态：</td>
                        <td><?php echo $order['order_status_text']; ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">创建时间：</td>
                        <td><?php echo $order['create_time_format']; ?></td>
                    </tr>
                </table>
            </div>

            <h4>订单商品明细</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>商品名称</th>
                        <th>商品单价</th>
                        <th>购买数量</th>
                        <th>商品小计</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_goods_list as $goods): ?>
                        <tr>
                            <td><?php echo $goods['goods_name']; ?></td>
                            <td>¥<?php echo $goods['goods_price_format']; ?></td>
                            <td><?php echo $goods['goods_num']; ?> 件</td>
                            <td>¥<?php echo $goods['sub_total_format']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold;">订单总金额：</td>
                        <td style="color: #f5222d; font-weight: bold;">¥<?php echo $order['order_amount_format']; ?></td>
                    </tr>
                </tbody>
            </table>

            <div style="text-align: center; margin-top: 30px;">
                <a href="../user/center.php" class="btn">返回我的订单</a>
                <?php if ($order['order_status'] == 0): ?>
                    <button class="btn btn-success" style="margin-left: 10px;" onclick="alert('该功能暂未实现，模拟支付成功！');">立即支付</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>