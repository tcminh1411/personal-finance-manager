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
<main class="px-4 py-5 max-w-7xl mx-auto md:px-6 md:py-6">

    <section id="addForm" class="bg-white border border-gray-200 rounded-2xl p-4 mb-5 md:p-6 scroll-mt-40">

        <h2 class="text-center text-2xl font-semibold text-gray-800 mb-4 flex items-center justify-center gap-2">Thêm
            Giao Dịch</h2>

        <form id="transactionForm" novalidate class="flex flex-col gap-4">
            <input type="hidden" id="transaction_id" name="id" value="">
            <div class="flex flex-col gap-1">
                <label for="amount" class="text-base font-medium text-gray-700">Số tiền</label>
                <input type="number" id="amount" name="amount" placeholder="Nhập số tiền (VD: 50000)" min="0" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex flex-col gap-1">
                <label for="type" class="text-base font-medium text-gray-700">Loại</label>
                <select id="type" name="type" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base
                               focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Chọn loại --</option>
                    <option value="income">Thu nhập</option>
                    <option value="expense">Chi tiêu</option>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label for="category" class="text-base font-medium text-gray-700">Danh mục</label>
                <select id="category" name="category_id" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base
                               focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Chọn danh mục (tùy chọn) --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>">
                        <?= e($cat['name']) ?> (<?= $cat['type'] === 'income' ? 'Thu' : 'Chi' ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-col gap-1">
                <label for="description" class="text-base font-medium text-gray-700">Mô tả</label>
                <input type="text" id="description" name="description" placeholder="Nhập nội dung (VD: Ăn sáng)"
                    required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex flex-col gap-1">
                <label for="date" class="text-base font-medium text-gray-700">Ngày</label>
                <input type="date" id="date" name="date" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-base
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium text-base
                           hover:bg-blue-700 active:scale-[0.98] transition-all mt-1">
                Thêm giao dịch
            </button>
            <button type="button" id="btnCancelEdit" class="w-full bg-gray-200 text-gray-800 py-2.5 rounded-lg font-medium text-base
                           hover:bg-gray-300 transition-all mt-2" style="display:none;">
                Hủy chỉnh sửa
            </button>

            <div id="notification"></div>
        </form>
    </section>
    <div class="border-t border-gray-100 my-5"></div>
    <section id="manager" class="scroll-mt-40">
        <h2 class="text-center text-2xl font-semibold text-gray-800 mb-4 flex items-center justify-center gap-2">
            <i class="ri-star-fill text-yellow-400 text-lg"></i>
            Quản lý
        </h2>

        <div class="grid grid-cols-1 gap-3 mb-5 sm:grid-cols-3">

            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <h3 class="text-base text-gray-500 mb-1">Tổng Thu</h3>
                <p class="text-xl font-medium text-green-600">
                    <?= formatMoney($totalIncome ?? 0) ?>
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <h3 class="text-base text-gray-500 mb-1">Tổng Chi</h3>
                <p class="text-xl font-medium text-red-500">
                    <?= formatMoney($totalExpense ?? 0) ?>
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <h3 class="text-base text-gray-500 mb-1">Số Dư</h3>
                <p class="text-xl font-medium text-gray-800">
                    <?= formatMoney(($totalIncome ?? 0) - ($totalExpense ?? 0)) ?>
                </p>
            </div>
        </div>
    </section>
    <div class="border-t border-gray-100 my-5"></div>
    <section id="charts" class="mb-5">
        <h2 class="text-center text-2xl font-semibold text-gray-800 mb-4 flex items-center justify-center gap-2">
            <i class="ri-bar-chart-box-line"></i> Phân Tích Chi Tiêu
        </h2>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="text-center text-base font-medium text-gray-700 mb-3">Chi Tiêu Theo Danh Mục</h3>
                <div class="relative h-48 md:h-56">
                    <canvas id="expensePieChart"></canvas>
                </div>
                <p class="text-sm text-gray-400 text-center mt-2">
                    Biểu đồ tròn thể hiện tỷ lệ chi tiêu theo từng danh mục
                </p>
            </div>

            <div class="text-center bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="text-base font-medium text-gray-700 mb-3">Thu Chi Theo Tháng</h3>
                <div class="relative h-48 md:h-56">
                    <canvas id="incomeExpenseBarChart"></canvas>
                </div>
                <p class="text-sm text-gray-400 text-center mt-2">
                    So sánh thu nhập và chi tiêu trong 12 tháng gần nhất
                </p>
            </div>
        </div>
    </section>
    <div class="border-t border-gray-100 my-5"></div>
    <section id="filter-section" class="scroll-mt-40">
        <div id="filter" class="text-center bg-white border border-gray-200 rounded-2xl p-4 mb-5">
            <h2 class="text-center text-2xl font-semibold text-gray-800 mb-4 flex items-center justify-center gap-2">Lọc
                & Tìm kiếm</h2>

            <div class="mb-3">
                <input type="text" id="filter-search" placeholder="Tìm kiếm theo mô tả..." class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-base
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-2 flex-wrap mb-3 justify-center">
                <button type="button" data-range="today" class="btn-shortcut text-sm px-3 py-1.5 border border-gray-300 rounded-full
                               hover:bg-gray-50 hover:border-blue-400 hover:text-blue-600 transition-colors">
                    Hôm nay
                </button>
                <button type="button" data-range="week" class="btn-shortcut text-sm px-3 py-1.5 border border-gray-300 rounded-full
                               hover:bg-gray-50 hover:border-blue-400 hover:text-blue-600 transition-colors">
                    Tuần này
                </button>
                <button type="button" data-range="month" class="btn-shortcut text-sm px-3 py-1.5 border border-gray-300 rounded-full
                               hover:bg-gray-50 hover:border-blue-400 hover:text-blue-600 transition-colors">
                    Tháng này
                </button>
            </div>

            <div class="flex flex-col gap-3 mb-3 sm:flex-row">
                <input type="date" id="filter-date-from" title="Từ ngày" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-lg text-base
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="date" id="filter-date-to" title="Đến ngày" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-lg text-base
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex flex-col gap-3 mb-3 sm:flex-row">
                <select id="filter-type" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-lg text-base
                               focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Tất cả loại --</option>
                    <option value="income">Thu</option>
                    <option value="expense">Chi</option>
                </select>
                <select id="filter-category" class="flex-1 px-3 py-2.5 border border-gray-200 rounded-lg text-base
                               focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>">
                        <?= e($cat['name']) ?> (<?= $cat['type'] === 'income' ? 'Thu' : 'Chi' ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex gap-2 flex-wrap justify-center">
                <button type="button" id="btnFilter" class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white
                               rounded-lg text-base hover:bg-blue-700 transition-colors">
                    <i class="ri-search-line text-sm"></i>Lọc
                </button>
                <button type="button" id="btnExport" class="flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white
                               rounded-lg text-base hover:bg-green-700 transition-colors">
                    <i class="ri-file-download-line text-sm"></i>Xuất CSV
                </button>
                <button type="button" id="btnReset" class="flex items-center gap-1.5 px-4 py-2 bg-gray-100 text-gray-700
                               border border-gray-200 rounded-lg text-base hover:bg-gray-200 transition-colors">
                    <i class="ri-refresh-line text-sm"></i>Reset
                </button>
            </div>

            <div class="mt-3 text-base text-gray-600">
                <div id="filter-info"></div>
                <div id="export-message"></div>
            </div>
        </div>
    </section>
    <div class="border-t border-gray-100 my-5"></div>
    <section id="transaction-list" class="scroll-mt-40">
        <h2 class="text-center text-2xl font-semibold text-gray-800 mb-4 flex items-center justify-center gap-2">Danh
            sách giao dịch</h2>
        <div id="pagination" class="mb-4">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                <div class="flex items-center gap-2 text-base text-gray-600">
                    <label for="per-page-select">Hiển thị:</label>
                    <select id="per-page-select" class="px-2 py-1.5 border border-gray-200 rounded-lg text-base bg-white
                                   focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                <button class="btn-prev w-8 h-8 flex items-center justify-center border border-gray-200
                                   rounded-lg text-base hover:bg-gray-50 disabled:opacity-40
                                   disabled:cursor-not-allowed transition-colors"
                    <?= $page <= 1 ? 'disabled' : '' ?>>‹</button>

                <?php
                    if ($totalPages > 1) {
                        $start = max(1, $page - 2);
                        $end   = min($totalPages, $page + 2);
                        if ($end - $start < 4) $start = max(1, $end - 4);
                        for ($i = $start; $i <= $end; $i++):
                    ?>
                <button class="btn-page w-8 h-8 flex items-center justify-center border rounded-lg
                                       text-base transition-colors <?= $i == $page
                                                                        ? 'bg-blue-600 text-white border-blue-600'
                                                                        : 'border-gray-200 hover:bg-gray-50' ?>"
                    data-page="<?= $i ?>">
                    <?= $i ?>
                </button>
                <?php
                        endfor;
                    }
                    ?>

                <button class="btn-next w-8 h-8 flex items-center justify-center border border-gray-200
                                   rounded-lg text-base hover:bg-gray-50 disabled:opacity-40
                                   disabled:cursor-not-allowed transition-colors"
                    <?= $page >= $totalPages ? 'disabled' : '' ?>>›</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="w-full text-base border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="sortable whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200 w-10">
                            STT</th>
                        <th class="sortable whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200 cursor-pointer hover:text-gray-800 select-none"
                            data-key="transaction_date">Ngày ↕</th>
                        <th class="sortable whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200 cursor-pointer hover:text-gray-800 select-none"
                            data-key="type">Loại ↕</th>
                        <th class="sortable whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200 cursor-pointer hover:text-gray-800 select-none"
                            data-key="category_name">Danh mục ↕</th>
                        <th class="sortable whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200 cursor-pointer hover:text-gray-800 select-none"
                            data-key="amount">Số tiền ↕</th>
                        <th class="sortable whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200 cursor-pointer hover:text-gray-800 select-none"
                            data-key="description">Mô tả ↕</th>
                        <th
                            class="whitespace-nowrap text-center text-sm font-medium text-gray-500 uppercase tracking-wide px-3 py-3 border-b border-gray-200">
                            Hành động</th>
                    </tr>
                </thead>
                <tbody id="txTableBody">
                    <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $index => $tx): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-3 py-3 border-b border-gray-100 text-gray-500 text-center whitespace-nowrap">
                            <?= $offset + $index + 1 ?></td>
                        <td class="px-3 py-3 border-b border-gray-100 whitespace-nowrap text-gray-700">
                            <?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                        <td class="px-3 py-3 border-b border-gray-100 whitespace-nowrap">
                            <?php if ($tx['type'] === 'income'): ?>
                            <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Thu
                                nhập</span>
                            <?php else: ?>
                            <span class="text-sm font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded-full">Chi
                                tiêu</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-3 border-b border-gray-100 text-gray-700">
                            <?php
                                    if ($tx['category_id'] && isset($categoryMap[$tx['category_id']])) {
                                        echo e($categoryMap[$tx['category_id']]['name']);
                                    } else {
                                        echo '<span class="text-gray-400 italic text-sm">Chưa phân loại</span>';
                                    }
                                    ?>
                        </td>
                        <td class="px-3 py-3 border-b border-gray-100 font-medium text-gray-800 whitespace-nowrap">
                            <?= formatMoney($tx['amount']) ?></td>
                        <td class="px-3 py-3 border-b border-gray-100 text-gray-600 max-w-[160px] truncate">
                            <?= e($tx['description']) ?></td>
                        <td class="px-3 py-3 border-b border-gray-100 whitespace-nowrap">
                            <div class="flex gap-1.5">
                                <button
                                    class="btn-edit text-sm px-2.5 py-1 border border-blue-200 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                                    data-id="<?= $tx['id'] ?>"
                                    data-category="<?= $tx['category_id'] ?? '' ?>">Sửa</button>
                                <button
                                    class="btn-delete text-sm px-2.5 py-1 border border-red-200 text-red-500 rounded-lg hover:bg-red-50 transition-colors"
                                    data-id="<?= $tx['id'] ?>">Xóa</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-10 text-base">Chưa có giao dịch.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>