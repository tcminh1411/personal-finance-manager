<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="auth-page">
    <div class="auth-container">
        <h1>🔐 Đăng Nhập</h1>

        <?php
        session_start();
        if (isset($_SESSION['login_error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
            unset($_SESSION['login_error']);
        }
        if (isset($_SESSION['register_success'])) {
            echo '<div class="success-message">' . htmlspecialchars($_SESSION['register_success']) . '</div>';
            unset($_SESSION['register_success']);
        }
        ?>

        <form action="auth/login-process" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-auth">Đăng Nhập</button>
        </form>
        <button type="button" class="btn-demo" onclick="demoLogin()">
            ⚡ Dùng tài khoản Demo
        </button>

        <script>
        function demoLogin() {
            document.getElementById('username').value = 'minh';
            document.getElementById('password').value = '141103';
            document.querySelector('.auth-form').submit();
        }
        </script>

        <style>
        .btn-demo {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background: #f0f4ff;
            color: #4a6cf7;
            border: 2px dashed #4a6cf7;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-demo:hover {
            background: #4a6cf7;
            color: white;
        }
        </style>

        <div class="auth-links">
            Chưa có tài khoản? <a href="register">Đăng ký ngay</a>
        </div>
    </div>
</body>

</html>
