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
    <header class="app-header">
        <div class="header-container">
            <h1 class="logo">Personal Finance Manager</h1>

            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-info">
                    <span class="text-gray-600">
                        <i class="ri-user-fill"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                    <a href="auth/logout.php" class="text-red-500 hover:text-red-700 transition">
                        <i class="ri-logout-circle-line"></i> Đăng xuất
                    </a>
                </div>
            <?php endif; ?>

            <nav class="main-nav">
                <ul class="nav-list">
                    <?php foreach (
                        [
                            '#addForm'          => 'Thêm GD',
                            '#manager'          => 'Quản lý',
                            '#filter-section'   => 'Lọc',
                            '#transaction-list' => 'Danh sách',
                        ] as $href => $label
                    ): ?>
                        <li>
                            <a href="<?= $href ?>" class="nav-link">
                                <?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </header>
    <div class="header-spacer"></div>