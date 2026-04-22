<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.8.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body class="auth-container">
    <div class="card-auth">
        <h1 class="auth-title">
            <i class="ri-lock-line text-blue-500"></i>
            Đăng Nhập
        </h1>

        <form action="auth/login-process" method="POST" class="form-auth">
            <div class="form-group">
                <label for="username" class="form-label-lg">
                    Tên đăng nhập
                </label>
                <input type="text" id="username" name="username" required autofocus class="form-input-lg">
            </div>

            <div class="form-group">
                <label for="password" class="form-label-lg">
                    Mật khẩu
                </label>
                <input type="password" id="password" name="password" required class="form-input-lg">
            </div>
            <?php
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert-error">'
                    . htmlspecialchars($_SESSION['login_error'])
                    . '</div>';
                unset($_SESSION['login_error']);
            }
            if (isset($_SESSION['register_success'])) {
                echo '<div class="alert-success">'
                    . htmlspecialchars($_SESSION['register_success'])
                    . '</div>';
                unset($_SESSION['register_success']);
            }
            ?>
            <button type="submit" class="btn-primary-lg">
                <i class="ri-login-circle-line mr-1"></i>Đăng Nhập
            </button>


            <button type="button" onclick="demoLogin()" class="btn-primary">
                <i class="ri-flashlight-line mr-1"></i>Dùng tài khoản Demo
            </button>
        </form>
        <p class="p-lg">
            Chưa có tài khoản?
            <a href="register" class="link-primary">Đăng ký ngay</a>
        </p>
    </div>

    <script>
        function demoLogin() {
            document.getElementById('username').value = 'minh';
            document.getElementById('password').value = '141103';
            document.querySelector('form').submit();
        }
    </script>
</body>

</html>