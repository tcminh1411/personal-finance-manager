<?php
require_once 'config/database.php';
require_once 'includes/helpers.php';

$totalIncome = 0;
$totalExpense = 0;

try {
  $sql = "SELECT * FROM transactions ORDER BY transaction_date DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();

  $transactions = $stmt->fetchAll();

  foreach ($transactions as $tx) {
    if ($tx['type'] === 'income') {
      $totalIncome += $tx['amount'];
    } else {
      $totalExpense += $tx['amount'];
    }
  }

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
        <label for="description">Mô tả</label>
        <input type="text" id="description" name="description" placeholder="Nhập nội dung (VD: Ăn sáng)" required />
      </div>

      <div class="form-group">
        <label for="date">Ngày</label>
        <input type="date" id="date" name="date" required />
      </div>

      <button type="submit">➕ ADD</button>

      <div id="notification"></div>
    </form>
  </section>

  <section>
    <h2>Quản lý</h2>

    <div class="financial-summary">
      <div class="summary-card">
        <h3>Tổng Thu</h3>
        <p class="value text-green">
          <?= formatMoney($totalIncome) ?>
        </p>
      </div>
      <div class="summary-card">
        <h3>Tổng Chi</h3>
        <p class="value text-red">
          <?= formatMoney($totalExpense) ?>
        </p>
      </div>
      <div class="summary-card">
        <h3>Số Dư</h3>
        <p class="value text-dark">
          <?= formatMoney($totalIncome - $totalExpense) ?>
        </p>
      </div>
    </div>

    <br />

    <div id="filter">
      <h3>Lọc dữ liệu</h3>
      <div class="filter-row">
        <div class="filter-col">
          <input type="date" id="filter-date" placeholder="Lọc theo ngày" />
        </div>
        <div class="filter-col">
          <select id="filter-type">
            <option value="">-- Tất cả loại --</option>
            <option value="income">Thu</option>
            <option value="expense">Chi</option>
          </select>
        </div>
      </div>

      <div id="filter-buttons">
        <button id="btnFilter" type="button">🔍 Lọc</button>
        <button id="btnReset" type="button" class="btn-reset">
          🔄 Reset
        </button>
      </div>
    </div>

    <hr class="separator" />

    <h3>Danh sách giao dịch</h3>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>STT</th>
            <th class="sortable" data-key="date" title="Bấm để xếp theo ngày">Ngày ↕</th>
            <th class="sortable" data-key="type" title="Bấm để xếp theo loại">Loại ↕</th>
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

                <td class="text-dark">
                  <?= formatMoney($tx['amount']) ?>
                </td>

                <td><?= e($tx['description']) ?></td>

                <td>
                  <button class="btn-edit" data-id="<?= $tx['id'] ?>">Sửa</button>
                  <button class="btn-delete" data-id="<?= $tx['id'] ?>">Xóa</button>
                </td>
              </tr>
            <?php endforeach; ?>

          <?php else: ?>
            <tr>
              <td colspan="6" class="empty-row">
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