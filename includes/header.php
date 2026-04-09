<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.8.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <header>
        <div class="header-content">
            <h1>Personal Finance Manager</h1>
            <?php if (isset($_SESSION['username'])): ?>
                <div class="header-user-section">
                    <span class="user-info">
                        <i class="ri-user-fill user-icon"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                    <a href="auth/logout.php" class="logout-btn">
                        <i class="ri-logout-circle-line logout-icon"></i> Đăng xuất
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>