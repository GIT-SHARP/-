<?php
// 创建订单：生成唯一订单号、扣减库存、插入订单数据、清空购物车
session_start();
require_once '../conn/db_conn.php';

$msg = '';

// 未登录，跳转登录页
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../user/login.php?msg=请先登录再创建订单");
    exit();
}

$user_id = $_SESSION['user_id'];
// 1. 查询当前用户购物车商品（验证是否有商品可结算）
$cart_sql = "SELECT c.goods_id, c.goods_num, g.goods_name, g.goods_price, g.goods_stock
        FROM `cart` c
        LEFT JOIN `goods` g ON c.goods_id = g.id
        WHERE c.user_id = ?";

$cart_stmt = mysqli_prepare($conn, $cart_sql);
mysqli_stmt_bind_param($cart_stmt, 'i', $user_id);
mysqli_stmt_execute($cart_stmt);
$cart_result = mysqli_stmt_get_result($cart_stmt);
$cart_list = [];
$total_amount = 0;

while ($row = mysqli_fetch_assoc($cart_result)) {
    if (empty($row['goods_id'])) continue;
    // 验证库存（防止中途库存变动）
    if ($row['goods_num'] > $row['goods_stock']) {
        $msg = '商品《' . $row['goods_name'] . '》库存不足，当前库存仅剩余 ' . $row['goods_stock'] . ' 件！';
        break;
    }
    // 计算商品小计和总金额
    $row['sub_total'] = $row['goods_price'] * $row['goods_num'];
    $total_amount += $row['sub_total'];
    $cart_list[] = $row;
}

if (!empty($msg)) {
    header("Location: ../cart/cart.php?msg=" . urlencode($msg));
    exit();
}

if (empty($cart_list)) {
    header("Location: ../cart/cart.php?msg=购物车为空，无法创建订单！");
    exit();
}

// 2. 开始事务（保证数据一致性：扣减库存、插入订单、清空购物车要么同时成功，要么同时失败）
mysqli_begin_transaction($conn);

try {
    // 3. 生成唯一订单号（时间戳+用户ID+随机数）
    $order_sn = date('YmdHis') . $user_id . mt_rand(1000, 9999);
    $order_status = 0; // 0-未支付，1-已支付
    $create_time = time();

    // 4. 插入订单主表
    $order_sql = "INSERT INTO `order_info` (order_sn, user_id, order_amount, order_status, create_time) VALUES (?, ?, ?, ?, ?)";
    $order_stmt = mysqli_prepare($conn, $order_sql);
    mysqli_stmt_bind_param($order_stmt, 'ssdii', $order_sn, $user_id, $total_amount, $order_status, $create_time);
    mysqli_stmt_execute($order_stmt);
    $order_id = mysqli_insert_id($conn); // 获取新增订单ID

    if ($order_id <= 0) {
        throw new Exception("插入订单主表失败！");
    }

    // 5. 插入订单商品明细表，并扣减商品库存
    foreach ($cart_list as $cart) {
        $goods_id = $cart['goods_id'];
        $goods_num = $cart['goods_num'];
        $goods_name = $cart['goods_name'];
        $goods_price = $cart['goods_price'];

        // 插入订单商品明细
        $order_goods_sql = "INSERT INTO `order_goods` (order_id, goods_id, goods_name, goods_price, goods_num) VALUES (?, ?, ?, ?, ?)";
        $order_goods_stmt = mysqli_prepare($conn, $order_goods_sql);
        mysqli_stmt_bind_param($order_goods_stmt, 'iisdi', $order_id, $goods_id, $goods_name, $goods_price, $goods_num);
        mysqli_stmt_execute($order_goods_stmt);

        // 扣减商品库存
        $update_goods_sql = "UPDATE `goods` SET goods_stock = goods_stock - ? WHERE id = ?";
        $update_goods_stmt = mysqli_prepare($conn, $update_goods_sql);
        mysqli_stmt_bind_param($update_goods_stmt, 'ii', $goods_num, $goods_id);
        mysqli_stmt_execute($update_goods_stmt);
    }

    // 6. 清空当前用户购物车
    $del_cart_sql = "DELETE FROM `cart` WHERE user_id = ?";
    $del_cart_stmt = mysqli_prepare($conn, $del_cart_sql);
    mysqli_stmt_bind_param($del_cart_stmt, 'i', $user_id);
    mysqli_stmt_execute($del_cart_stmt);

    // 7. 提交事务
    mysqli_commit($conn);
    header("Location: order_detail.php?order_id=" . $order_id . "&msg=订单创建成功！");
    exit();

} catch (Exception $e) {
    // 8. 回滚事务（出错时撤销所有操作）
    mysqli_rollback($conn);
    $msg = '创建订单失败：' . $e->getMessage();
    header("Location: ../cart/cart.php?msg=" . urlencode($msg));
    exit();
}
?>