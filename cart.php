<?php
// 引入数据库连接文件
require_once '../conn/db_conn.php';

// 验证用户是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: /user/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = intval($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>我的购物车</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <div class="container">
        <!-- 导航栏 -->
        <div class="nav">
            <a href="/goods/index.php">首页</a>
            <a href="/cart/cart.php">我的购物车</a>
            <a href="/user/center.php">个人中心</a>
            <a href="/user/login.php?action=logout">退出登录</a>
            <a href="/admin/login.php" target="_blank">后台管理</a>
        </div>

        <!-- 购物车列表 -->
        <h2>我的购物车</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>商品图片</th>
                    <th>商品名称</th>
                    <th>商品单价</th>
                    <th>购买数量</th>
                    <th>商品小计</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 查询购物车商品（关联商品表）
                $sql = "SELECT c.id as cart_id, c.num, g.* FROM cart c LEFT JOIN goods g ON c.goods_id = g.id WHERE c.user_id = {$user_id} ORDER BY c.create_time DESC";
                $result = mysqli_query($conn, $sql);
                $total_price = 0; // 购物车总金额

                if (mysqli_num_rows($result) > 0) {
                    while ($cart = mysqli_fetch_assoc($result)) {
                        $sub_total = $cart['price'] * $cart['num'];
                        $total_price += $sub_total;
                ?>
                <tr>
                    <td><img src="<?php echo $cart['goods_img']; ?>" alt="<?php echo $cart['goods_name']; ?>" style="width: 50px; height: 50px; object-fit: contain;"></td>
                    <td><?php echo $cart['goods_name']; ?></td>
                    <td>¥ <?php echo number_format($cart['price'], 2); ?></td>
                    <td>
                        <form method="post" action="/cart/update_cart.php" style="display: inline;">
                            <input type="hidden" name="cart_id" value="<?php echo $cart['cart_id']; ?>">
                            <input type="number" name="num" value="<?php echo $cart['num']; ?>" min="1" max="<?php echo $cart['stock']; ?>" style="width: 60px; padding: 5px;">
                            <button type="submit" style="padding: 3px 10px; font-size: 12px;">修改</button>
                        </form>
                    </td>
                    <td>¥ <?php echo number_format($sub_total, 2); ?></td>
                    <td><a href="/cart/del_cart.php?cart_id=<?php echo $cart['cart_id']; ?>" class="del-btn" onclick="return confirm('确定要删除该商品吗？')">删除</a></td>
                </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='6'>购物车为空，快去添加商品吧～</td></tr>";
                }
                mysqli_free_result($result);
                ?>
            </tbody>
        </table>

        <!-- 购物车底部 -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div style="text-align: right; font-size: 18px; margin-bottom: 20px;">
                购物车总金额：<span style="color: #ff6700; font-weight: bold;">¥ <?php echo number_format($total_price, 2); ?></span>
            </div>
            <div style="text-align: right;">
                <a href="/order/create_order.php" class="goods-btn">创建订单</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>