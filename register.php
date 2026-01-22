<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="auth-page">
    <div class="auth-container">
        <h1>📝 Đăng Ký Tài Khoản</h1>

        <?php
        session_start();
        if (isset($_SESSION['register_error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['register_error']) . '</div>';
            unset($_SESSION['register_error']);
        }
        ?>

        <form action="auth/register-process.php" method="POST" class="auth-form" id="registerForm">
            <div class="form-group">
                <label for="username">Tên đăng nhập *</label>
                <input type="text" id="username" name="username" required autofocus minlength="3" maxlength="50"
                    pattern="\w+" title="Chỉ chứa chữ cái, số và dấu gạch dưới">
                <small style="color: #7f8c8d; font-size: 12px;">
                    3-50 ký tự, chỉ chữ cái, số và dấu gạch dưới
                </small>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu *</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small style="color: #7f8c8d; font-size: 12px;">
                    Tối thiểu 6 ký tự
                </small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Xác nhận mật khẩu *</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
            </div>

            <button type="submit" class="btn-auth">Đăng Ký</button>
        </form>

        <div class="auth-links">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </div>
    </div>

    <script>
        // Client-side validation
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const passwordConfirm = document.getElementById('password_confirm');

        form.addEventListener('submit', (e) => {
            if (password.value !== passwordConfirm.value) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                passwordConfirm.focus();
            }
        });

        // Real-time password match feedback
        passwordConfirm.addEventListener('input', () => {
            if (passwordConfirm.value && password.value !== passwordConfirm.value) {
                passwordConfirm.classList.add('error');
            } else {
                passwordConfirm.classList.remove('error');
            }
        });
    </script>
</body>

</html>