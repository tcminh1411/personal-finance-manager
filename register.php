<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.8.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body class="auth-container">
    <div class="card-auth">

        <h1 class="auth-title">
            <i class="ri-user-add-fill text-blue-500"></i>
            Đăng Ký Tài Khoản
        </h1>

        <?php
        if (isset($_SESSION['register_error'])) {
            echo '<div class="alert-error">'
                . htmlspecialchars($_SESSION['register_error'])
                . '</div>';
            unset($_SESSION['register_error']);
        }
        ?>

        <form action="auth/register-process.php" method="POST" class="form-auth" id="registerForm">
            <div class="form-group">
                <label for="username" class="form-label-lg">
                    Tên đăng nhập <span class="required-star">*</span>
                </label>
                <input type="text" id="username" name="username" required autofocus minlength="3" maxlength="50"
                    pattern="\w+" title="Chỉ chứa chữ cái, số và dấu gạch dưới" class="form-input-lg">
                <p class="p-sm">3-50 ký tự, chỉ chữ cái, số và dấu gạch dưới</p>
            </div>

            <div class="form-group">
                <label for="password" class="form-label-lg">
                    Mật khẩu <span class="required-star">*</span>
                </label>
                <input type="password" id="password" name="password" required minlength="6" class="form-input-lg">
                <p class="p-sm">Tối thiểu 6 ký tự</p>
            </div>

            <div class="form-group">
                <label for="password_confirm" class="form-label-lg">
                    Xác nhận mật khẩu <span class="required-star">*</span>
                </label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6"
                    class="form-input-lg">

            </div>
            <p id="pw-mismatch" class="alert-error hidden">Mật khẩu không khớp</p>
            <button type="submit" class="btn-primary-lg">
                Đăng Ký
            </button>
        </form>

        <p class="p-lg">
            Đã có tài khoản?
            <a href="login.php" class="link-primary">Đăng nhập ngay</a>
        </p>
    </div>

    <script>
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');
        const mismatchMsg = document.getElementById('pw-mismatch');

        form.addEventListener('submit', (e) => {
            if (password.value !== passwordConfirm.value) {
                e.preventDefault();
                passwordConfirm.focus();
            }
        });

        passwordConfirm.addEventListener('input', () => {
            const mismatch = passwordConfirm.value && password.value !== passwordConfirm.value;
            passwordConfirm.classList.toggle('input-error', mismatch);
            mismatchMsg.classList.toggle('hidden', !mismatch);
        });
    </script>
</body>

</html>