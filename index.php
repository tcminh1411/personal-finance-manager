<?php
require_once 'auth/check-auth.php';
require_once 'config/database.php';
require_once 'includes/helpers.php';

$current_user_id = $_SESSION['user_id'];

$page  = isset($_GET['page'])  ? max(1, (int) $_GET['page'])              : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit']))   : 10;
$offset = ($page - 1) * $limit;

$totalRecords = 0;
$totalPages = 0;
$categoryMap  = [];
$categories = [];
$transactions = [];
$totalIncome  = 0;
$totalExpense = 0;

try {
    $sqlCat = "SELECT id, name, type FROM categories ORDER BY type, name";
    $stmtCat = $pdo->prepare($sqlCat);
    $stmtCat->execute();
    $categories = $stmtCat->fetchAll();
    foreach ($categories as $cat) {
        $categoryMap[$cat['id']] = ['name' => $cat['name'], 'type' => $cat['type']];
    }

    $sqlCount = "SELECT COUNT(*) as total FROM transactions WHERE user_id = :user_id";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute([':user_id' => $current_user_id]);
    $countResult  = $stmtCount->fetch();
    $totalRecords = (int) ($countResult['total'] ?? 0);
    $totalPages   = $totalRecords > 0 ? (int) ceil($totalRecords / $limit) : 0;

    $sql = "SELECT * FROM transactions
            WHERE user_id = :user_id
            ORDER BY transaction_date DESC, id DESC
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit',   $limit,           PDO::PARAM_INT);
    $stmt->bindValue(':offset',  $offset,          PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll();

    $sqlTotal = "SELECT
        SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as total_expense
        FROM transactions WHERE user_id = :user_id";
    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute([':user_id' => $current_user_id]);
    $totals      = $stmtTotal->fetch();
    $totalIncome  = $totals['total_income']  ?? 0;
    $totalExpense = $totals['total_expense'] ?? 0;
} catch (PDOException $e) {
    die("Lỗi lấy dữ liệu: " . $e->getMessage());
}

require_once 'includes/header.php';
?>
<main class="main-container">

    <section id="manager" class="section-form">
        <h2 class="section-title">
            <i class="ri-star-fill text-yellow-400 text-lg"></i>
            Quản lý
        </h2>
        <div class="stats-grid">
            <div class="card">
                <h3 class="stat-header">Tổng Thu</h3>
                <p id="summary-income" class="stat-value text-green-600">
                    <?= formatMoney($totalIncome ?? 0) ?>
                </p>
            </div>
            <div class="card">
                <h3 class="stat-header">Tổng Chi</h3>
                <p id="summary-expense" class="stat-value text-red-500">
                    <?= formatMoney($totalExpense ?? 0) ?>
                </p>
            </div>
            <div class="card">
                <h3 class="stat-header">Số Dư</h3>
                <p id="summary-balance" class="stat-value text-gray-600">
                    <?= formatMoney(($totalIncome ?? 0) - ($totalExpense ?? 0)) ?>
                </p>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <section id="charts" class="section-form">
        <h2 class="section-title">
            <i class="ri-bar-chart-box-line"></i> Phân Tích Chi Tiêu
        </h2>
        <div class="chart-grid">
            <div class="card">
                <h3 class="chart-header">Chi Tiêu Theo Danh Mục</h3>
                <div class="relative h-48 md:h-56">
                    <canvas id="expensePieChart"></canvas>
                </div>
                <p class="chart-text">
                    Biểu đồ tròn thể hiện tỷ lệ chi tiêu theo từng danh mục
                </p>
            </div>
            <div class="card">
                <h3 class="chart-header">Thu Chi Theo Tháng</h3>
                <div class="chart-container">
                    <canvas id="incomeExpenseBarChart"></canvas>
                </div>
                <p class="chart-text">
                    So sánh thu nhập và chi tiêu trong 12 tháng gần nhất
                </p>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <section id="addForm" class="section-form">
        <h2 class="section-title"><i class="ri-add-box-line"></i> Thêm Giao Dịch</h2>
        <form id="transactionForm" novalidate class="form-grid">
            <input type="hidden" id="transaction_id" name="id" value="">
            <div class="form-group">
                <label for="amount" class="form-label">Số tiền</label>
                <input type="number" id="amount" name="amount" placeholder="Nhập số tiền (VD: 50000)" min="0" required
                    class="form-input">
            </div>
            <div class="form-group">
                <label for="type" class="form-label">Loại</label>
                <select id="type" name="type" required class="form-select">
                    <option value="">Chọn loại</option>
                    <option value="income">Thu nhập</option>
                    <option value="expense">Chi tiêu</option>
                </select>
            </div>
            <div class="form-group">
                <label for="category" class="form-label">Danh mục</label>
                <select id="category" name="category_id" class="form-select">
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>">
                            <?= e($cat['name']) ?> (<?= $cat['type'] === 'income' ? 'Thu' : 'Chi' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Mô tả</label>
                <input type="text" id="description" name="description" placeholder="Nhập nội dung (VD: Ăn sáng)"
                    required class="form-input">
            </div>
            <div class="form-group">
                <label for="date" class="form-label">Ngày</label>
                <input type="date" id="date" name="date" required class="form-input">
            </div>
            <div class="md:col-span-1 form-group">
                <div class="form-label">
                    <p></p>Thao tác
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">
                        Thêm giao dịch
                    </button>
                    <button type="button" id="btnCancelEdit" class="btn-primary hidden">
                        Hủy chỉnh sửa
                    </button>
                </div>
            </div>
            <div id="notification" class="full-width text-center text-sm font-semibold py-3 rounded-xl"></div>
        </form>
    </section>

    <div class="divider"></div>

    <section id="filter-section" class="section-form">
        <h2 class="section-title">Lọc & Tìm kiếm</h2>
        <div class="mb-3">
            <input type="text" id="filter-search" placeholder="Tìm kiếm theo mô tả..." class="form-input">
        </div>
        <div class="filter-group">
            <button type="button" data-range="yesterday" class="btn-filter-range hidden hide-mobile">Hôm
                qua</button>
            <button type="button" data-range="today" class="btn-filter-range">Hôm nay</button>
            <button type="button" data-range="week" class="btn-filter-range">Tuần này</button>
            <button type="button" data-range="month" class="btn-filter-range">Tháng này</button>
            <button type="button" data-range="year" class="btn-filter-range hidden hide-mobile">Năm này</button>
        </div>
        <div class="filter-row">
            <input type="date" id="filter-date-from" title="Từ ngày" class="form-input" placeholder="Từ ngày">
            <input type="date" id="filter-date-to" title="Đến ngày" class="form-input" placeholder="Đến ngày">
        </div>
        <div class="filter-row">
            <select id="filter-type" class="form-select">
                <option value="">Tất cả loại</option>
                <option value="income">Thu</option>
                <option value="expense">Chi</option>
            </select>
            <select id="filter-category" class="form-select">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>">
                        <?= e($cat['name']) ?> (<?= $cat['type'] === 'income' ? 'Thu' : 'Chi' ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <button type="button" id="btnFilter" class="btn-control bg-blue-400 hover:bg-blue-600 focus:ring-blue-500">
                <i class="ri-filter-line text-sm"></i>Lọc
            </button>
            <button type="button" id="btnExport"
                class="btn-control bg-green-400 hover:bg-green-600 focus:ring-green-500">
                <i class="ri-file-download-line text-sm"></i>Xuất CSV
            </button>
            <button type="button" id="btnReset" class="btn-control bg-gray-400 hover:bg-gray-600 focus:ring-gray-500">
                <i class="ri-refresh-line text-sm"></i>Reset
            </button>
        </div>
        <div class="mt-3 text-sm text-gray-500 text-center">
            <div id="filter-info"></div>
            <div id="export-message"></div>
        </div>
    </section>

    <div class="divider"></div>

    <section id="transaction-list" class="section-form">
        <h2 class="section-title">Danh
            sách giao dịch</h2>
        <div id="pagination" class="mb-4">
            <div class="flex items-center justify-between flex-wrap gap-3 mb-3">
                <div class="flex items-center gap-3 text-base text-gray-600">
                    <label for="per-page-select" class="form-label whitespace-nowrap">Hiển thị:</label>
                    <select id="per-page-select" class="form-select">
                        <option value="10" <?= $limit == 10  ? 'selected' : '' ?>>10 dòng</option>
                        <option value="25" <?= $limit == 25  ? 'selected' : '' ?>>25 dòng</option>
                        <option value="50" <?= $limit == 50  ? 'selected' : '' ?>>50 dòng</option>
                        <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 dòng</option>
                    </select>
                </div>
                <div id="pagination-info" class="text-base text-gray-500">
                    <?php if ($totalRecords > 0): ?>
                        Hiển thị
                        <?= min(($page - 1) * $limit + 1, $totalRecords) ?>-<?= min($page * $limit, $totalRecords) ?>
                        trong tổng số <?= $totalRecords ?> giao dịch
                    <?php else: ?>
                        Không có giao dịch
                    <?php endif; ?>
                </div>
            </div>
            <div id="pagination-controls" class="flex justify-center items-center gap-1 flex-wrap">
                <?php if ($totalPages > 0): ?>
                    <button class="btn-page" data-action="first" <?= $page <= 1 ? 'disabled' : '' ?>>
                        <i class="ri-skip-left-line"></i>
                    </button>
                    <button class="btn-page" data-action="prev" <?= $page <= 1 ? 'disabled' : '' ?>></button>

                    <?php
                    if ($totalPages > 1) {
                        $start = max(1, $page - 2);
                        $end   = min($totalPages, $page + 2);
                        if ($end - $start < 4) $start = max(1, $end - 4);
                        for ($i = $start; $i <= $end; $i++):
                    ?>
                            <button class="btn-page <?= $i == $page ? 'btn-page-active' : '' ?>" data-action="page"
                                data-page="<?= $i ?>">
                            </button>
                    <?php
                        endfor;
                    }
                    ?>
                    <button class="btn-page" data-action="next" <?= $page >= $totalPages ? 'disabled' : '' ?>></button>
                    <button class="btn-page" data-action="last" <?= $page >= $totalPages ? 'disabled' : '' ?>>
                        <i class="ri-skip-right-line"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-base border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th w-10">
                            STT</th>
                        <th class="table-th" data-key="transaction_date" data-sortable>Ngày ↕</th>
                        <th class="table-th" data-key="type" data-sortable>Loại ↕</th>
                        <th class="table-th" data-key="category_name" data-sortable>Danh mục ↕</th>
                        <th class="table-th" data-key="amount" data-sortable>Số tiền ↕</th>
                        <th class="table-th" data-key="description" data-sortable>Mô tả ↕</th>
                        <th
                            class="whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200">
                            Hành động</th>
                    </tr>
                </thead>
                <tbody id="txTableBody">
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-10">
                            Đang tải...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>