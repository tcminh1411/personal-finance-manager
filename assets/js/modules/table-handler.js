/**
 * Table Handler Module
 * Manages transaction table operations (Edit, Delete, Update)
 */
const TableHandler = {
  /**
   * Initialize table handler
   * Sets up event delegation for edit and delete buttons
   */
  init() {
    this.setupTableActions();
  },

  /**
   * Setup event listeners for table action buttons
   * Uses event delegation for better performance
   */
  setupTableActions() {
    const tableBody = document.getElementById("txTableBody");
    if (!tableBody) return;

    tableBody.addEventListener("click", (e) => {
      const btnDelete = e.target.closest(".btn-delete");
      const btnEdit = e.target.closest(".btn-edit");

      if (btnDelete) {
        const id = btnDelete.dataset.id;
        this.handleDelete(id);
      }

      if (btnEdit) {
        const id = btnEdit.dataset.id;
        const row = btnEdit.closest("tr");
        const categoryId = btnEdit.dataset.category;
        FormHandler.fillFormForEdit(id, row, categoryId);
      }
    });
  },

  /**
   * Handle delete transaction with confirmation
   * @async
   * @param {number|string} id - Transaction ID to delete
   */
  async handleDelete(id) {
    if (!confirm("Bạn có chắc chắn muốn xóa không?")) return;

    try {
      const formData = new FormData();
      formData.append("id", id);

      const response = await fetch("api/transactions/delete.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        window.location.reload();
      } else {
        alert("Lỗi xóa: " + data.message);
      }
    } catch (error) {
      alert("Lỗi kết nối server");
      if (window.location.hostname === "localhost") {
        console.error("Delete transaction error:", error);
      }
    }
  },

  /**
   * Update table with new transaction data (REPLACE mode)
   * Used for pagination and filtering
   * @param {Array<Object>} transactions - Array of transaction objects
   * @param {number} startIndex - Starting row number for display
   */
  updateTable(transactions, startIndex = 0) {
    const tbody = document.getElementById("txTableBody");
    if (!tbody) return;

    if (transactions.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7" class="empty-row">
            Không tìm thấy giao dịch nào.
          </td>
        </tr>
      `;
      return;
    }

    let html = "";
    transactions.forEach((tx, index) => {
      html += this.generateRowHTML(tx, startIndex + index + 1);
    });

    tbody.innerHTML = html;
  },

  /**
   * Append new rows to existing table (APPEND mode)
   * Used for "Load More" functionality
   * @param {Array<Object>} transactions - Array of transaction objects
   */
  appendRows(transactions) {
    const tbody = document.getElementById("txTableBody");
    if (!tbody) return;

    // Remove empty state if exists
    const emptyRow = tbody.querySelector(".empty-row");
    if (emptyRow) {
      tbody.innerHTML = "";
    }

    // Calculate starting row number
    const currentRows = tbody.querySelectorAll("tr").length;

    let html = "";
    transactions.forEach((tx, index) => {
      html += this.generateRowHTML(tx, currentRows + index + 1);
    });

    tbody.insertAdjacentHTML("beforeend", html);
  },

  /**
   * Generate HTML string for a single table row
   * @param {Object} tx - Transaction object
   * @param {number} tx.id - Transaction ID
   * @param {string} tx.type - Transaction type ('income' or 'expense')
   * @param {number} tx.amount - Transaction amount
   * @param {string} tx.description - Transaction description
   * @param {string} tx.transaction_date - Transaction date (ISO format)
   * @param {string|null} tx.category_name - Category name
   * @param {number|null} tx.category_id - Category ID
   * @param {number} rowNumber - Row number for display
   * @returns {string} HTML string for table row
   */
  generateRowHTML(tx, rowNumber) {
    const isIncome = tx.type === "income";
    const typeText = isIncome ? "Thu nhập" : "Chi tiêu";
    const typeClass = isIncome ? "text-green" : "text-red";
    const categoryName =
      tx.category_name || '<span class="category-unset">Chưa phân loại</span>';

    const dateObj = new Date(tx.transaction_date);
    const date = dateObj.toLocaleDateString("vi-VN");
    const amount = Utils.formatMoney(tx.amount);

    return `
      <tr>
        <td>${rowNumber}</td>
        <td>${date}</td>
        <td><span class="${typeClass}">${typeText}</span></td>
        <td>${categoryName}</td>
        <td class="text-dark">${amount}</td>
        <td>${Utils.escapeHtml(tx.description)}</td>
        <td>
          <button class="btn-edit" data-id="${tx.id}" data-category="${
            tx.category_id || ""
          }">Sửa</button>
          <button class="btn-delete" data-id="${tx.id}">Xóa</button>
        </td>
      </tr>
    `;
  },

  /**
   * Update financial summary cards with new data
   * @param {Object} summary - Summary statistics
   * @param {number} summary.total_income - Total income amount
   * @param {number} summary.total_expense - Total expense amount
   * @param {number} summary.balance - Balance (income - expense)
   */
  updateSummary(summary) {
    const incomeEl = document.querySelector(
      ".summary-card:nth-child(1) .value",
    );
    const expenseEl = document.querySelector(
      ".summary-card:nth-child(2) .value",
    );
    const balanceEl = document.querySelector(
      ".summary-card:nth-child(3) .value",
    );

    if (incomeEl) {
      incomeEl.textContent = Utils.formatMoney(summary.total_income);
    }

    if (expenseEl) {
      expenseEl.textContent = Utils.formatMoney(summary.total_expense);
    }

    if (balanceEl) {
      balanceEl.textContent = Utils.formatMoney(summary.balance);
    }
  },
};
