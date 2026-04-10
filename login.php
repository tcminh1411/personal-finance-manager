<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.8.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body
    class="min-h-screen bg-[url('../images/background.jpg')] bg-cover bg-center flex flex-col items-center justify-center px-4 py-8">
    <div class="w-full sm:max-w-sm bg-white rounded-2xl border border-gray-200 p-6">
        <h1 class="text-2xl font-medium text-gray-800 flex items-center justify-center gap-2 mb-6">
            <i class="ri-lock-line text-blue-500"></i>
            Đăng Nhập
        </h1>

        <?php
        session_start();
        if (isset($_SESSION['login_error'])) {
            echo '<div class="bg-red-50 border border-red-200 text-red-700 text-lg px-4 py-3 rounded-lg mb-4">'
                . htmlspecialchars($_SESSION['login_error'])
                . '</div>';
            unset($_SESSION['login_error']);
        }
        if (isset($_SESSION['register_success'])) {
            echo '<div class="bg-green-50 border border-green-200 text-green-700 text-lg px-4 py-3 rounded-lg mb-4">'
                . htmlspecialchars($_SESSION['register_success'])
                . '</div>';
            unset($_SESSION['register_success']);
        }
        ?>

        <form action="auth/login-process" method="POST" class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <label for="username" class="text-lg font-medium text-gray-700">
                    Tên đăng nhập
                </label>
                <input type="text" id="username" name="username" required autofocus class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-lg
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex flex-col gap-1">
                <label for="password" class="text-lg font-medium text-gray-700">
                    Mật khẩu
                </label>
                <input type="password" id="password" name="password" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-lg
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium text-lg
                       hover:bg-blue-700 active:scale-[0.98] transition-all mt-2">
                <i class="ri-login-circle-line mr-1"></i>Đăng Nhập
            </button>
        </form>

        <button type="button" onclick="demoLogin()" class="w-full mt-3 py-2.5 border-2 border-dashed border-blue-300 text-blue-600
                   rounded-lg text-lg hover:bg-blue-50 transition-colors">
            <i class="ri-flashlight-line mr-1"></i>Dùng tài khoản Demo
        </button>
        <p class="text-center text-lg text-gray-500 mt-5">
            Chưa có tài khoản?
            <a href="register" class="text-blue-600 hover:underline font-medium">Đăng ký ngay</a>
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