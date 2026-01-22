<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Personal Finance Manager</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <header>
        <div class="header-content">
            <h1>Personal Finance Manager</h1>
            <?php if (isset($_SESSION['username'])): ?>
                <div class="header-user-section">
                    <span class="user-info">
                        👤 <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                    <a href="auth/logout.php" class="logout-btn">
                        🚪 Đăng xuất
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>