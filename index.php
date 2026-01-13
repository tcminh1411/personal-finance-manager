<?php
require_once 'config/database.php';
require_once 'includes/helpers.php';

// ===== PAGINATION - LẤY TỪ URL NẾU CÓ =====
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;

// ===== TẠO CATEGORY MAP ĐỂ TRÁNH N+1 =====
$categoryMap = [];

try {
  // Lấy danh sách categories và tạo map
  $sqlCat = "SELECT id, name, type FROM categories ORDER BY type, name";
  $stmtCat = $pdo->prepare($sqlCat);
  $stmtCat->execute();
  $categories = $stmtCat->fetchAll();

  // Tạo map id -> name
  foreach ($categories as $cat) {
    $categoryMap[$cat['id']] = [
      'name' => $cat['name'],
      'type' => $cat['type']
    ];
  }

  // Get transactions với pagination
  $sql = "SELECT * FROM transactions ORDER BY transaction_date DESC LIMIT :limit OFFSET :offset";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $transactions = $stmt->fetchAll();

  // Calculate totals (ALL data)
  $sqlTotal = "SELECT
                 SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                 SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
               FROM transactions";
  $stmtTotal = $pdo->prepare($sqlTotal);
  $stmtTotal->execute();
  $totals = $stmtTotal->fetch();

  $totalIncome = $totals['total_income'] ?? 0;
  $totalExpense = $totals['total_expense'] ?? 0;

} catch (PDOException $e) {
  die("Lỗi lấy dữ liệu: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<main>
  <section id="addForm">
    <h2>Thêm Giao Dịch</h2>
    <form id="transactionForm" novalidate>
      <input type="hidden" id="transaction_id" name="id" value="">

      <div class="form-group">
        <label for="amount">Số tiền</label>
        <input type="number" id="amount" name="amount" placeholder="Nhập số tiền (VD: 50000)" min="0" required />
      </div>

      <div class="form-group">
        <label for="type">Loại</label>
        <select id="type" name="type" required>
          <option value="">-- Chọn loại --</option>
          <option value="income">Thu nhập</option>
          <option value="expense">Chi tiêu</option>
        </select>
      </div>

      <div class="form-group">
        <label for="category">Danh mục</label>
        <select id="category" name="category_id">
          <option value="">-- Chọn danh mục (tùy chọn) --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>">
              <?= e($cat['name']) ?> (<?= $cat['type'] === 'income' ? 'Thu' : 'Chi' ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="description">Mô tả</label>
        <input type="text" id="description" name="description" placeholder="Nhập nội dung (VD: Ăn sáng)" required />
      </div>

      <div class="form-group">
        <label for="date">Ngày</label>
        <input type="date" id="date" name="date" required />
      </div>

      <button type="submit" class="btn">➕ ADD</button>
      <div id="notification"></div>
    </form>
  </section>

  <section>
    <h2>Quản lý</h2>
    <div class="financial-summary">
      <div class="summary-card">
        <h3>Tổng Thu</h3>
        <p class="value text-green">
          <?= formatMoney($totalIncome ?? 0) ?>
        </p>
      </div>
      <div class="summary-card">
        <h3>Tổng Chi</h3>
        <p class="value text-red">
          <?= formatMoney($totalExpense ?? 0) ?>
        </p>
      </div>
      <div class="summary-card">
        <h3>Số Dư</h3>
        <p class="value text-dark">
          <?= formatMoney(($totalIncome ?? 0) - ($totalExpense ?? 0)) ?>
        </p>
      </div>
    </div>

    <hr class="separator" />

    <div id="filter">
      <h3>Lọc & Tìm kiếm</h3>

      <!-- Search box -->
      <div class="filter-row">
        <div class="filter-col-full">
          <input type="text" id="filter-search" placeholder="🔍 Tìm kiếm theo mô tả..." />
        </div>
      </div>

      <!-- Date Range Shortcuts -->
      <div class="date-shortcuts">
        <button type="button" class="btn btn-shortcut" data-range="today">Hôm nay</button>
        <button type="button" class="btn btn-shortcut" data-range="week">Tuần này</button>
        <button type="button" class="btn btn-shortcut" data-range="month">Tháng này</button>
      </div>

      <div class="filter-row">
        <div class="filter-col">
          <input type="date" id="filter-date-from" title="Từ ngày" />
        </div>
        <div class="filter-col">
          <input type="date" id="filter-date-to" title="Đến ngày" />
        </div>
      </div>

      <div class="filter-row">
        <div class="filter-col">
          <select id="filter-type">
            <option value="">-- Tất cả loại --</option>
            <option value="income">Thu</option>
            <option value="expense">Chi</option>
          </select>
        </div>

        <div class="filter-col">
          <select id="filter-category">
            <option value="">-- Tất cả danh mục --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>">
                <?= e($cat['name']) ?> (<?= $cat['type'] === 'income' ? 'Thu' : 'Chi' ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="filter-actions">
        <button type="button" class="btn btn-filter" id="btnFilter">🔍 Lọc</button>
        <button type="button" class="btn btn-export" id="btnExport">📥 Xuất CSV</button>
        <button type="button" class="btn btn-reset" id="btnReset">🔄 Reset</button>
      </div>

      <!-- Filter Results -->
      <div class="filter-result-box">
        <div id="filter-info"></div>
        <div id="export-message"></div>
      </div>
    </div>

    <hr class="separator" />

    <!-- ===== NEW: PAGINATION SECTION ===== -->
    <div id="pagination">
      <div class="pagination-settings">
        <div class="per-page-selector">
          <label for="per-page-select">Hiển thị:</label>
          <select id="per-page-select">
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 dòng</option>
            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25 dòng</option>
            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 dòng</option>
            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 dòng</option>
          </select>
        </div>
        <div id="pagination-info">
          <?php if ($totalRecords > 0): ?>
            Hiển thị <?= min(($page - 1) * $limit + 1, $totalRecords) ?>-<?= min($page * $limit, $totalRecords) ?>
            trong tổng số <?= $totalRecords ?> giao dịch
          <?php else: ?>
            Không có giao dịch
          <?php endif; ?>
        </div>
      </div>
      <div id="pagination-controls">
        <?php if ($totalPages > 0): ?>
          <button class="btn-prev" <?= $page <= 1 ? 'disabled' : '' ?>>‹</button>
          <?php
          if ($totalPages > 1) {
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            if ($end - $start < 4) {
              $start = max(1, $end - 4);
            }
            for ($i = $start; $i <= $end; $i++):
              ?>
              <button class="btn-page <?= $i == $page ? 'active' : '' ?>" data-page="<?= $i ?>">
                <?= $i ?>
              </button>
              <?php
            endfor;
          }
          ?>
          <button class="btn-next" <?= $page >= $totalPages ? 'disabled' : '' ?>>›</button>
        <?php endif; ?>
      </div>
    </div>
    <!-- =================================== -->

    <h3>Danh sách giao dịch</h3>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>STT</th>
            <th class="sortable" data-key="transaction_date" title="Bấm để xếp theo ngày">Ngày ↕</th>
            <th class="sortable" data-key="type" title="Bấm để xếp theo loại">Loại ↕</th>
            <th class="sortable" data-key="category_name" title="Bấm để xếp theo danh mục">Danh mục ↕</th>
            <th class="sortable" data-key="amount" title="Bấm để xếp theo tiền">Số tiền ↕</th>
            <th class="sortable" data-key="description" title="Bấm để xếp theo tên">Mô tả ↕</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody id="txTableBody">
          <?php if (count($transactions) > 0): ?>
            <?php foreach ($transactions as $index => $tx): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= date('d/m/Y', strtotime($tx['transaction_date'])) ?></td>
                <td>
                  <?php if ($tx['type'] === 'income'): ?>
                    <span class="text-green">Thu nhập</span>
                  <?php else: ?>
                    <span class="text-red">Chi tiêu</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  if ($tx['category_id'] && isset($categoryMap[$tx['category_id']])) {
                    echo e($categoryMap[$tx['category_id']]['name']);
                  } else {
                    echo '<span class="category-unset">Chưa phân loại</span>';
                  }
                  ?>
                </td>
                <td class="text-dark">
                  <?= formatMoney($tx['amount']) ?>
                </td>
                <td><?= e($tx['description']) ?></td>
                <td>
                  <button class="btn btn-edit" data-id="<?= $tx['id'] ?>"
                    data-category="<?= $tx['category_id'] ?? '' ?>">Sửa</button>
                  <button class="btn btn-delete" data-id="<?= $tx['id'] ?>">Xóa</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="empty-row">
                Chưa có giao dịch.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<?php
require_once 'includes/footer.php';
?>