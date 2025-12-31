<?php
// 添加购物车：验证登录、验证库存、插入/更新购物车
session_start();
require_once '../conn/db_conn.php';

$msg = '';

// 未登录，跳转登录页
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: ../user/login.php?msg=请先登录再添加购物车");
    exit();
}

// 验证参数
if (empty($_GET['goods_id']) || !is_numeric($_GET['goods_id']) || empty($_GET['goods_num']) || !is_numeric($_GET['goods_num'])) {
    $msg = '无效的商品参数！';
} else {
    $user_id = $_SESSION['user_id'];
    $goods_id = intval($_GET['goods_id']);
    $goods_num = intval($_GET['goods_num']);

    // 验证商品是否存在及库存是否充足
    $goods_sql = "SELECT id, goods_name, goods_stock FROM `goods` WHERE id = ? LIMIT 1";
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
        // 检查该商品是否已在购物车中（存在则更新数量，不存在则插入）
        $cart_sql = "SELECT id, goods_num FROM `cart` WHERE user_id = ? AND goods_id = ? LIMIT 1";
        $cart_stmt = mysqli_prepare($conn, $cart_sql);
        mysqli_stmt_bind_param($cart_stmt, 'ii', $user_id, $goods_id);
        mysqli_stmt_execute($cart_stmt);
        $cart_result = mysqli_stmt_get_result($cart_stmt);
        $cart = mysqli_fetch_assoc($cart_result);

        if (!empty($cart)) {
            // 已存在，更新数量（累加）
            $new_goods_num = $cart['goods_num'] + $goods_num;
            // 验证更新后的数量是否超过库存
            if ($new_goods_num > $goods['goods_stock']) {
                $msg = '购物车中该商品数量+本次添加数量超过库存，当前库存仅剩余 ' . $goods['goods_stock'] . ' 件！';
            } else {
                $update_sql = "UPDATE `cart` SET goods_num = ?, create_time = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                $create_time = time();
                mysqli_stmt_bind_param($update_stmt, 'iii', $new_goods_num, $create_time, $cart['id']);
                if (mysqli_stmt_execute($update_stmt)) {
                    header("Location: cart.php?msg=购物车更新成功！");
                    exit();
                } else {
                    $msg = '更新购物车失败：' . mysqli_error($conn);
                }
            }
        } else {
            // 不存在，插入购物车
            $insert_sql = "INSERT INTO `cart` (user_id, goods_id, goods_num, create_time) VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            $create_time = time();
            mysqli_stmt_bind_param($insert_stmt, 'iiii', $user_id, $goods_id, $goods_num, $create_time);
            if (mysqli_stmt_execute($insert_stmt)) {
                header("Location: cart.php?msg=添加购物车成功！");
                exit();
            } else {
                $msg = '添加购物车失败：' . mysqli_error($conn);
            }
        }
    }
}

// 有错误，跳转购物车并提示
header("Location: cart.php?msg=" . urlencode($msg));
exit();
?>