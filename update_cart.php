<?php
// 修改购物车商品数量：验证登录、验证库存、更新数据
session_start();
require_once '../conn/db_conn.php';

$msg = '';

// 未登录，跳转登录页
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../user/login.php?msg=请先登录再修改购物车");
    exit();
}

// 验证POST参数
if (empty($_POST['cart_id']) || !is_numeric($_POST['cart_id']) || empty($_POST['goods_num']) || !is_numeric($_POST['goods_num'])) {
    $msg = '无效的修改参数！';
} else {
    $user_id = $_SESSION['user_id'];
    $cart_id = intval($_POST['cart_id']);
    $goods_num = intval($_POST['goods_num']);

    // 1. 验证购物车商品是否属于当前用户
    $cart_sql = "SELECT id, goods_id FROM `cart` WHERE id = ? AND user_id = ? LIMIT 1";
    $cart_stmt = mysqli_prepare($conn, $cart_sql);
    mysqli_stmt_bind_param($cart_stmt, 'ii', $cart_id, $user_id);
    mysqli_stmt_execute($cart_stmt);
    $cart_result = mysqli_stmt_get_result($cart_stmt);
    $cart = mysqli_fetch_assoc($cart_result);

    if (empty($cart)) {
        $msg = '该购物车商品不存在或不属于你！';
    } else {
        $goods_id = $cart['goods_id'];
        // 2. 验证库存是否充足
        $goods_sql = "SELECT id, goods_stock FROM `goods` WHERE id = ? LIMIT 1";
        $goods_stmt = mysqli_prepare($conn, $goods_sql);
        mysqli_stmt_bind_param($goods_stmt, 'i', $goods_id);
        mysqli_stmt_execute($goods_stmt);
        $goods_result = mysqli_stmt_get_result($goods_stmt);
        $goods = mysqli_fetch_assoc($goods_result);

        if (empty($goods)) {
            $msg = '该商品不存在或已被删除！';
        } elseif ($goods_num < 1) {
            $msg = '购买数量不能小于1！';
        } elseif ($goods_num > $goods['goods_stock']) {
            $msg = '库存不足，当前库存仅剩余 ' . $goods['goods_stock'] . ' 件！';
        } else {
            // 3. 更新购物车商品数量
            $update_sql = "UPDATE `cart` SET goods_num = ?, create_time = ? WHERE id = ? AND user_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            $create_time = time();
            mysqli_stmt_bind_param($update_stmt, 'iiii', $goods_num, $create_time, $cart_id, $user_id);
            if (mysqli_stmt_execute($update_stmt)) {
                header("Location: cart.php?msg=购物车商品数量修改成功！");
                exit();
            } else {
                $msg = '修改购物车商品数量失败：' . mysqli_error($conn);
            }
        }
    }
}

// 有错误，跳转购物车并提示
header("Location: cart.php?msg=" . urlencode($msg));
exit();
?>