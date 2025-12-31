<?php
// 后台商品管理：验证管理员登录、添加商品、删除商品、展示商品列表
session_start();
require_once '../conn/db_conn.php';

$msg = '';

// 验证管理员是否登录
if (!isset($_SESSION['admin_is_login']) || !isset($_SESSION['admin_name']) || $_SESSION['admin_is_login'] !== true) {
    header("Location: login.php?msg=请先以管理员身份登录");
    exit();
}

// 处理商品添加（POST提交）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['goods_name'])) {
    $goods_name = trim($_POST['goods_name']);
    $goods_price = trim($_POST['goods_price']);
    $goods_stock = trim($_POST['goods_stock']);
    $goods_desc = trim($_POST['goods_desc'] ?? '');
    $goods_img = 'public/images/default.jpg'; // 默认图片

    // 表单验证
    if (empty($goods_name) || empty($goods_price) || !is_numeric($goods_price) || empty($goods_stock) || !is_numeric($goods_stock)) {
        $msg = '商品名称、价格、库存不能为空，且价格和库存必须为数字！';
    } elseif ($goods_price <= 0 || $goods_stock < 0) {
        $msg = '商品价格必须大于0，库存不能小于0！';
    } else {
        // 处理商品图片上传（【留白：若需完善图片上传功能，可补充此处代码】）
        $create_time = time();

        // 插入商品表
        $insert_sql = "INSERT INTO `goods` (goods_name, goods_price, goods_stock, goods_img, goods_desc, create_time) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, 'sdissi', $goods_name, $goods_price, $goods_stock, $goods_img, $goods_desc, $create_time);

        if (mysqli_stmt_execute($insert_stmt)) {
            $msg = '商品添加成功！';
        } else {
            $msg = '商品添加失败：' . mysqli_error($conn);
        }
    }
}

// 处理商品删除
if (!empty($_GET['action']) && $_GET['action'] === 'del' && !empty($_GET['goods_id']) && is_numeric($_GET['goods_id'])) {
    $goods_id = intval($_GET['goods_id']);

    // 删除商品
    $del_sql = "DELETE FROM `goods` WHERE id = ?";
    $del_stmt = mysqli_prepare($conn, $del_sql);
    mysqli_stmt_bind_param($del_stmt, 'i', $goods_id);

    if (mysqli_stmt_execute($del_stmt)) {
        header("Location: goods_manage.php?msg=商品删除成功！");
        exit();
    } else {
        $msg = '商品删除失败：' . mysqli_error($conn);
    }
}

// 查询所有商品（用于展示）
$goods_list = [];
$sql = "SELECT id, goods_name, goods_price, goods_stock, goods_img, create_time FROM `goods` ORDER BY create_time DESC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    // 处理商品默认图片
    if (empty($row['goods_img']) || !file_exists('../' . $row['goods_img'])) {
        $row['goods_img'] = 'public/images/default.jpg';
    }
    // 格式化价格和时间
    $row['goods_price_format'] = number_format($row['goods_price'], 2);
    $row['create_time_format'] = date('Y-m-d H:i:s', $row['create_time']);
    $goods_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>商品管理 - 电商系统后台</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h2>电商系统后台管理</h2>
            <div>
                <span class="user-info">欢迎，管理员：<?php echo $_SESSION['admin_name']; ?></span>
                <a href="login.php?action=logout" style="color: #f0f9ff;">退出登录</a>
            </div>
        </div>
    </div>

    <div class="container">
        <h3>商品管理 - 添加商品</h3>
        <div class="msg <?php echo strpos($msg, '失败') !== false ? 'msg-error' : 'msg-success'; ?>"><?php echo $msg; ?></div>

        <div class="form-box" style="width: 600px;">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-item">
                    <label for="goods_name">商品名称：</label>
                    <input type="text" id="goods_name" name="goods_name" placeholder="请输入商品名称" value="<?php echo $_POST['goods_name'] ?? ''; ?>">
                </div>
                <div class="form-item">
                    <label for="goods_price">商品价格：</label>
                    <input type="number" id="goods_price" name="goods_price" step="0.01" min="0.01" placeholder="请输入商品价格" value="<?php echo $_POST['goods_price'] ?? ''; ?>">
                </div>
                <div class="form-item">
                    <label for="goods_stock">商品库存：</label>
                    <input type="number" id="goods_stock" name="goods_stock" min="0" placeholder="请输入商品库存" value="<?php echo $_POST['goods_stock'] ?? 0; ?>">
                </div>
                <div class="form-item">
                    <label for="goods_desc">商品描述（选填）：</label>
                    <textarea id="goods_desc" name="goods_desc" placeholder="请输入商品描述"><?php echo $_POST['goods_desc'] ?? ''; ?></textarea>
                </div>
                <div class="form-item">
                    <label for="goods_img">商品图片（选填）：</label>
                    <input type="file" id="goods_img" name="goods_img" accept="image/jpg, image/jpeg, image/png">
                    <small style="color: #666; margin-left: 100px;">【留白：图片上传功能暂未完善，当前使用默认图片】</small>
                </div>
                <div class="form-item" style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">添加商品</button>
                </div>
            </form>
        </div>

        <hr style="margin: 50px 0; border: 1px solid #eee;">

        <h3>商品管理 - 商品列表</h3>
        <?php if (empty($goods_list)): ?>
            <div style="text-align: center; margin: 50px 0; color: #666;">
                暂无商品，快去添加商品吧！
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>商品图片</th>
                        <th>商品名称</th>
                        <th>商品单价</th>
                        <th>商品库存</th>
                        <th>添加时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($goods_list as $goods): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo $goods['goods_img']; ?>" alt="<?php echo $goods['goods_name']; ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td><?php echo $goods['goods_name']; ?></td>
                            <td>¥<?php echo $goods['goods_price_format']; ?></td>
                            <td><?php echo $goods['goods_stock']; ?> 件</td>
                            <td><?php echo $goods['create_time_format']; ?></td>
                            <td>
                                <a href="goods_manage.php?action=del&goods_id=<?php echo $goods['id']; ?>" class="btn btn-danger" onclick="return confirm('确定要删除该商品吗？删除后无法恢复！');">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>