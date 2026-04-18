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
    <header class="fixed top-0 left-0 w-full shadow-md z-50 bg-gray-200">
        <div
            class="flex flex-col items-center gap-2 py-2 px-4 md:flex-row md:flex-wrap md:justify-between md:px-6 lg:flex-nowrap lg:gap-4">

            <h1 class="text-2xl font-bold text-gray-800 md:shrink-0">
                Personal Finance Manager
            </h1>

            <?php if (isset($_SESSION['username'])): ?>
            <div class="flex items-center text-lg gap-4 md:order-2 md:shrink-0 lg:order-3">
                <span class="text-gray-600">
                    <i class="ri-user-fill"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
                <a href="auth/logout.php" class="text-red-500 hover:text-red-700 transition">
                    <i class="ri-logout-circle-line"></i> Đăng xuất
                </a>
            </div>
            <?php endif; ?>

            <nav class="w-full flex justify-center py-1 md:order-3 lg:order-2 lg:flex-1 lg:w-auto">
                <ul class="flex gap-1 md:gap-3 font-medium">
                    <?php foreach ([
                    '#addForm'          => 'Thêm GD',
                    '#manager'          => 'Quản lý',
                    '#filter-section'   => 'Lọc',
                    '#transaction-list' => 'Danh sách',
                ] as $href => $label): ?>
                    <li>
                        <a href="<?= $href ?>"
                            class="px-3 py-1.5 rounded-full hover:bg-blue-50 hover:text-blue-600 transition flex items-center gap-1">
                            <?= $label ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

        </div>
    </header>
    <div class="h-32 md:h-16 lg:h-8"></div>