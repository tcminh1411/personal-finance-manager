<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.8.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body>
    <header class="fixed top-0 left-0 w-full bg-white shadow-md z-50">
        <div class="flex flex-col items-center py-1 px-2">
            <h1 class="text-2xl font-bold text-gray-800">Personal Finance Manager</h1>
            <?php if (isset($_SESSION['username'])): ?>
                <div class="flex items-center text-lg gap-4 mt-4">
                    <span class="text-gray-600">
                        <i class="ri-user-fill"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                    <a href="auth/logout.php" class="text-red-500 hover:text-red-700 transition">
                        <i class="ri-logout-circle-line"></i> Đăng xuất
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <nav class="flex justify-center px-4 py-2 text-base">
            <ul class="flex gap-1 md:gap-3 font-medium">
                <li><a href="#addForm"
                        class="px-3 py-1.5 rounded-full hover:bg-green-50 hover:text-green-600 transition flex items-center gap-1">Thêm
                        GD</a></li>
                <li><a href="#manager"
                        class="px-3 py-1.5 rounded-full hover:bg-green-50 hover:text-green-600 transition flex items-center gap-1">Quản
                        lý</a></li>
                <li><a href="#filter-section"
                        class="px-3 py-1.5 rounded-full hover:bg-green-50 hover:text-green-600 transition flex items-center gap-1">Lọc</a>
                </li>
                <li><a href="#transaction-list"
                        class="px-3 py-1.5 rounded-full hover:bg-green-50 hover:text-green-600 transition flex items-center gap-1">Danh
                        sách</a></li>
            </ul>
        </nav>
    </header>
    <div class="h-36"></div>