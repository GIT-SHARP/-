<?php
// 用户登录：MD5密码验证、Session保持登录状态
session_start();
require_once '../conn/db_conn.php';

$msg = '';
// 接收跳转传递的提示信息
if (!empty($_GET['msg'])) {
    $msg = $_GET['msg'];
}

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

    // 2. 表单验证
    if (empty($username) || empty($password)) {
        $msg = '用户名和密码不能为空！';
    } else {
        // 3. 查询用户信息（预处理语句防SQL注入）
        $sql = "SELECT id, username, password FROM `user` WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // 4. 密码验证（MD5加密对比）
        if (!$user) {
            $msg = '该用户名未注册，请先注册！';
        } elseif (md5($password) !== $user['password']) {
            $msg = '密码错误，请重新输入！';
        } else {
            // 5. 登录成功，设置Session保持状态
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: ../goods/index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>用户登录 - 电商系统</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h2>电商系统</h2>
            <div>
                <a href="../goods/index.php">首页</a>
                <a href="register.php">用户注册</a>
            </div>
        </div>
    </div>

    <div class="form-box">
        <h3 style="text-align: center; margin-bottom: 20px;">用户登录</h3>
        <div class="msg <?php echo !empty($msg) ? 'msg-error' : ''; ?>"><?php echo $msg; ?></div>
        <form method="POST" action="">
            <div class="form-item">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username" placeholder="请输入用户名" value="<?php echo $_POST['username'] ?? ''; ?>">
            </div>
            <div class="form-item">
                <label for="password">密码：</label>
                <input type="password" id="password" name="password" placeholder="请输入密码">
            </div>
            <div class="form-item" style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn">登录</button>
            </div>
            <div style="text-align: center; margin-top: 15px;">
                暂无账号？<a href="register.php" style="color: #4285F4;">立即注册</a>
            </div>
        </form>
    </div>
</body>
</html>