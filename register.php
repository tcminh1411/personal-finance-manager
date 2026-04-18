<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.8.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body
    class="min-h-screen bg-[url('../images/background.jpg')] bg-cover bg-center flex flex-col items-center justify-center px-4 py-8">

    <div class="w-full sm:max-w-sm bg-white rounded-2xl border border-gray-200 p-6">

        <h1 class="text-2xl font-medium text-gray-800 flex items-center gap-2 mb-6">
            <i class="ri-user-add-fill text-blue-500"></i>
            Đăng Ký Tài Khoản
        </h1>

        <?php
        session_start();
        if (isset($_SESSION['register_error'])) {
            echo '<div class="bg-red-50 border border-red-200 text-red-700 text-lg px-4 py-3 rounded-lg mb-4">'
                . htmlspecialchars($_SESSION['register_error'])
                . '</div>';
            unset($_SESSION['register_error']);
        }
        ?>

        <form action="auth/register-process.php" method="POST" class="flex flex-col gap-4" id="registerForm">
            <div class="flex flex-col gap-1">
                <label for="username" class="text-lg font-medium text-gray-700">
                    Tên đăng nhập <span class="text-red-400">*</span>
                </label>
                <input type="text" id="username" name="username" required autofocus minlength="3" maxlength="50"
                    pattern="\w+" title="Chỉ chứa chữ cái, số và dấu gạch dưới" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-lg
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-sm text-gray-400">3-50 ký tự, chỉ chữ cái, số và dấu gạch dưới</p>
            </div>

            <div class="flex flex-col gap-1">
                <label for="password" class="text-lg font-medium text-gray-700">
                    Mật khẩu <span class="text-red-400">*</span>
                </label>
                <input type="password" id="password" name="password" required minlength="6" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-lg
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-sm text-gray-400">Tối thiểu 6 ký tự</p>
            </div>

            <div class="flex flex-col gap-1">
                <label for="password_confirm" class="text-lg font-medium text-gray-700">
                    Xác nhận mật khẩu <span class="text-red-400">*</span>
                </label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-lg
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           transition-colors">
                <p id="pw-mismatch" class="text-sm text-red-500 hidden">Mật khẩu không khớp</p>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium text-lg
                       hover:bg-blue-700 active:scale-[0.98] transition-all mt-2">
                Đăng Ký
            </button>
        </form>

        <p class="text-center text-lg text-gray-500 mt-5">
            Đã có tài khoản?
            <a href="login.php" class="text-blue-600 hover:underline font-medium">Đăng nhập ngay</a>
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
            passwordConfirm.classList.toggle('border-red-400', mismatch);
            passwordConfirm.classList.toggle('ring-1', mismatch);
            passwordConfirm.classList.toggle('ring-red-400', mismatch);
            mismatchMsg.classList.toggle('hidden', !mismatch);
        });
    </script>
</body>

</html>