<?php
// 商品首页：展示所有商品，提供详情页和加入购物车入口
session_start();
require_once '../conn/db_conn.php';

$goods_list = [];

// 查询所有商品（按添加时间倒序）
$sql = "SELECT id, goods_name, goods_price, goods_stock, goods_img, create_time FROM `goods` ORDER BY create_time DESC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    // 处理商品默认图片（若无图片，使用默认图）
    if (empty($row['goods_img']) || !file_exists('../' . $row['goods_img'])) {
        $row['goods_img'] = 'public/images/default.jpg';
    }
    // 格式化价格
    $row['goods_price_format'] = number_format($row['goods_price'], 2);
    $goods_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>商品首页 - 电商系统</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h2>电商系统</h2>
            <div>
                <a href="index.php" style="font-weight: bold;">首页</a>
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['username'])): ?>
                    <a href="../cart/cart.php">我的购物车</a>
                    <a href="../user/center.php">个人中心</a>
                    <span class="user-info">欢迎，<?php echo $_SESSION['username']; ?></span>
                    <a href="../user/center.php?action=logout" style="color: #f0f9ff;">退出登录</a>
                <?php else: ?>
                    <a href="../user/login.php">用户登录</a>
                    <a href="../user/register.php">用户注册</a>
                <?php endif; ?>
                <a href="../admin/login.php" style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 4px;">后台管理</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h3>商品列表</h3>
        <?php if (empty($goods_list)): ?>
            <div style="text-align: center; margin: 50px 0; color: #666;">
                暂无商品，请等待管理员添加！
            </div>
        <?php else: ?>
            <div class="goods-list">
                <?php foreach ($goods_list as $goods): ?>
                    <div class="goods-item">
                        <img src="../<?php echo $goods['goods_img']; ?>" alt="<?php echo $goods['goods_name']; ?>">
                        <div class="goods-name" title="<?php echo $goods['goods_name']; ?>">
                            <?php echo $goods['goods_name']; ?>
                        </div>
                        <div class="goods-price">¥<?php echo $goods['goods_price_format']; ?></div>
                        <div class="goods-stock">库存：<?php echo $goods['goods_stock']; ?> 件</div>
                        <div>
                            <a href="detail.php?goods_id=<?php echo $goods['id']; ?>" class="btn" style="margin-right: 10px;">查看详情</a>
                            <a href="../cart/add_cart.php?goods_id=<?php echo $goods['id']; ?>&goods_num=1" class="btn btn-success">加入购物车</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>