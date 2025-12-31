<?php
// 商品详情页：展示商品完整信息，支持加入购物车
session_start();
require_once '../conn/db_conn.php';

$msg = '';
$goods = [];

// 验证商品ID是否传入
if (empty($_GET['goods_id']) || !is_numeric($_GET['goods_id'])) {
    $msg = '无效的商品ID！';
} else {
    $goods_id = intval($_GET['goods_id']);
    // 查询商品详情
    $sql = "SELECT id, goods_name, goods_price, goods_stock, goods_img, goods_desc, create_time FROM `goods` WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $goods_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $goods = mysqli_fetch_assoc($result);

    if (empty($goods)) {
        $msg = '该商品不存在或已被删除！';
    } else {
        // 处理商品默认图片
        if (empty($goods['goods_img']) || !file_exists('../' . $goods['goods_img'])) {
            $goods['goods_img'] = 'public/images/default.jpg';
        }
        // 格式化价格
        $goods['goods_price_format'] = number_format($goods['goods_price'], 2);
        // 格式化添加时间
        $goods['create_time_format'] = date('Y-m-d H:i:s', $goods['create_time']);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo $goods['goods_name'] ?? '商品详情 - 电商系统'; ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h2>电商系统</h2>
            <div>
                <a href="index.php">首页</a>
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                    <a href="../cart/cart.php">我的购物车</a>
                    <a href="../user/center.php">个人中心</a>
                <?php else: ?>
                    <a href="../user/login.php">用户登录</a>
                    <a href="../user/register.php">用户注册</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="container">
            <div class="msg msg-error" style="text-align: center;"><?php echo $msg; ?></div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn">返回首页</a>
            </div>
        </div>
    <?php else: ?>
        <div class="detail-container">
            <img src="../<?php echo $goods['goods_img']; ?>" alt="<?php echo $goods['goods_name']; ?>">
            <h3><?php echo $goods['goods_name']; ?></h3>
            <div class="goods-price">¥<?php echo $goods['goods_price_format']; ?></div>
            <div class="goods-stock">库存：<?php echo $goods['goods_stock']; ?> 件 | 添加时间：<?php echo $goods['create_time_format']; ?></div>
            <div class="goods-desc">
                <h4>商品描述：</h4>
                <p><?php echo nl2br($goods['goods_desc']) ?: '该商品暂无详细描述'; ?></p>
            </div>
            <div>
                <a href="index.php" class="btn" style="margin-right: 10px;">返回商品列表</a>
                <a href="../cart/add_cart.php?goods_id=<?php echo $goods['id']; ?>&goods_num=1" class="btn btn-success">加入购物车</a>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>