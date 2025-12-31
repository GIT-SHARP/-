<?php
// 删除购物车商品：验证登录、验证权限、删除数据
session_start();
require_once '../conn/db_conn.php';

$msg = '';

// 未登录，跳转登录页
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../user/login.php?msg=请先登录再删除购物车商品");
    exit();
}

// 验证参数
if (empty($_GET['cart_id']) || !is_numeric($_GET['cart_id'])) {
    $msg = '无效的购物车商品ID！';
} else {
    $user_id = $_SESSION['user_id'];
    $cart_id = intval($_GET['cart_id']);

    // 验证该购物车商品是否属于当前用户
    $cart_sql = "SELECT id FROM `cart` WHERE id = ? AND user_id = ? LIMIT 1";
    $cart_stmt = mysqli_prepare($conn, $cart_sql);
    mysqli_stmt_bind_param($cart_stmt, 'ii', $cart_id, $user_id);
    mysqli_stmt_execute($cart_stmt);
    $cart_result = mysqli_stmt_get_result($cart_stmt);
    $cart = mysqli_fetch_assoc($cart_result);

    if (empty($cart)) {
        $msg = '该购物车商品不存在或不属于你！';
    } else {
        // 删除购物车商品
        $del_sql = "DELETE FROM `cart` WHERE id = ? AND user_id = ?";
        $del_stmt = mysqli_prepare($conn, $del_sql);
        mysqli_stmt_bind_param($del_stmt, 'ii', $cart_id, $user_id);
        if (mysqli_stmt_execute($del_stmt)) {
            header("Location: cart.php?msg=购物车商品删除成功！");
            exit();
        } else {
            $msg = '删除购物车商品失败：' . mysqli_error($conn);
        }
    }
}

// 有错误，跳转购物车并提示
header("Location: cart.php?msg=" . urlencode($msg));
exit();
?>