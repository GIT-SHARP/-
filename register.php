<?php
// 用户注册：用户名唯一性验证、MD5加密密码、插入用户表
session_start();
require_once '../conn/db_conn.php';

$msg = '';

// 若已登录，直接跳转首页
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    header("Location: ../goods/index.php");
    exit();
}

// 处理POST表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. 接收并过滤表单数据
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $repassword = trim($_POST['repassword'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // 2. 表单验证
    if (empty($username) || empty($password) || empty($repassword)) {
        $msg = '用户名、密码、确认密码不能为空！';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $msg = '用户名长度需在3-20个字符之间！';
    } elseif ($password !== $repassword) {
        $msg = '两次输入的密码不一致！';
    } else {
        // 3. 验证用户名唯一性（预处理语句防SQL注入）
        $check_sql = "SELECT id FROM `user` WHERE username = ? LIMIT 1";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, 's', $username);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $msg = '该用户名已被注册，请更换其他用户名！';
        } else {
            // 4. MD5加密密码，插入用户表
            $md5_pwd = md5($password);
            $create_time = time();

            $insert_sql = "INSERT INTO `user` (username, password, email, create_time) VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, 'sssi', $username, $md5_pwd, $email, $create_time);

            if (mysqli_stmt_execute($insert_stmt)) {
                // 注册成功，跳转登录页
                header("Location: login.php?msg=注册成功，请登录");
                exit();
            } else {
                $msg = '注册失败：' . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户注册 - 电商系统</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h2>电商系统</h2>
            <div>
                <a href="../goods/index.php">首页</a>
                <a href="login.php">用户登录</a>
            </div>
        </div>
    </div>

    <div class="form-box">
        <h3 style="text-align: center; margin-bottom: 20px;">用户注册</h3>
        <div class="msg <?php echo !empty($msg) ? 'msg-error' : ''; ?>"><?php echo $msg; ?></div>
        <form method="POST" action="">
            <div class="form-item">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username" placeholder="3-20位字符" value="<?php echo $_POST['username'] ?? ''; ?>">
            </div>
            <div class="form-item">
                <label for="password">密码：</label>
                <input type="password" id="password" name="password" placeholder="请输入密码">
            </div>
            <div class="form-item">
                <label for="repassword">确认密码：</label>
                <input type="password" id="repassword" name="repassword" placeholder="再次输入密码">
            </div>
            <div class="form-item">
                <label for="email">邮箱（选填）：</label>
                <input type="email" id="email" name="email" placeholder="请输入邮箱" value="<?php echo $_POST['email'] ?? ''; ?>">
            </div>
            <div class="form-item" style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn">注册</button>
            </div>
            <div style="text-align: center; margin-top: 15px;">
                已有账号？<a href="login.php" style="color: #4285F4;">立即登录</a>
            </div>
        </form>
    </div>
</body>
</html>