/**
 * Table Handler Module (Tailwind version)
 * Manages transaction table operations (Edit, Delete, Update)
 */
const TableHandler = {
    /**
     * Initialize table handler
     */
    init() {
        this.setupTableActions();
    },

    /**
     * Setup event listeners for table action buttons (event delegation)
     */
    setupTableActions() {
        const tableBody = document.getElementById("txTableBody");
        if (!tableBody) return;

        tableBody.addEventListener("click", (e) => {
            const btnDelete = e.target.closest("[data-action='delete']");
            const btnEdit = e.target.closest("[data-action='edit']");

            if (btnDelete) {
                const id = btnDelete.dataset.id;
                this.handleDelete(id);
            }

            if (btnEdit) {
                const id = btnEdit.dataset.id;
                const row = btnEdit.closest("tr");
                const categoryId = btnEdit.dataset.category;
                // Giả sử FormHandler đã được định nghĩa
                if (typeof FormHandler !== "undefined" && FormHandler.setEditMode) {
                    FormHandler.setEditMode(id, row, categoryId);
                } else {
                    console.error("FormHandler not found");
                }
            }
        });
    },

    /**
     * Handle delete transaction with confirmation
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
     * Replace entire table body with new transactions (REPLACE mode)
     * @param {Array<Object>} transactions - List of transaction objects
     * @param {number} startIndex - Starting row number (default 0)
     */
    updateTable(transactions, startIndex = 0) {
        const tbody = document.getElementById("txTableBody");
        if (!tbody) return;

        if (transactions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-gray-400 py-10 text-base">
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
     * @param {Array<Object>} transactions - List of transaction objects
     */
    appendRows(transactions) {
        const tbody = document.getElementById("txTableBody");
        if (!tbody) return;

        // Remove empty state if exists
        const emptyRow = tbody.querySelector(".text-center.text-gray-400");
        if (emptyRow && tbody.children.length === 1) {
            tbody.innerHTML = "";
        }

        const currentRows = tbody.querySelectorAll("tr").length;
        let html = "";
        transactions.forEach((tx, index) => {
            html += this.generateRowHTML(tx, currentRows + index + 1);
        });

        tbody.insertAdjacentHTML("beforeend", html);
    },

    /**
     * Generate HTML for a single table row (Tailwind classes)
     * @param {Object} tx - Transaction object
     * @param {number} rowNumber - Row number to display
     * @returns {string} HTML string
     */
    generateRowHTML(tx, rowNumber) {
        const isIncome = tx.type === "income";
        const typeText = isIncome ? "Thu nhập" : "Chi tiêu";
        const dateObj = new Date(tx.transaction_date);
        const date = dateObj.toLocaleDateString("vi-VN");
        const amount = Utils.formatMoney(tx.amount);
        const categoryDisplay = tx.category_name
            ? Utils.escapeHtml(tx.category_name)
            : '<span class="text-gray-400 italic text-sm">Chưa phân loại</span>';

        const badgeClass = isIncome
            ? "text-sm font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full"
            : "text-sm font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded-full";

        return `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-3 py-3 border-b border-gray-100 text-gray-500 text-center whitespace-nowrap">${rowNumber}</td>
                <td class="px-3 py-3 border-b border-gray-100 text-center whitespace-nowrap text-gray-700">${date}</td>
                <td class="px-3 py-3 border-b border-gray-100 text-center whitespace-nowrap">
                    <span class="${badgeClass}">${typeText}</span>
                </td>
                <td class="px-3 py-3 border-b border-gray-100 text-gray-700">${categoryDisplay}</td>
                <td class="px-3 py-3 border-b border-gray-100 font-medium text-gray-800 whitespace-nowrap">${amount}</td>
                <td class="px-3 py-3 border-b border-gray-100 text-gray-600 max-w-40 truncate">${Utils.escapeHtml(tx.description)}</td>
                <td class="px-3 py-3 border-b border-gray-100 whitespace-nowrap">
                    <div class="flex gap-1.5 justify-center">
                        <button class="text-sm px-2.5 py-1 border border-blue-200 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors" data-id="${tx.id}" data-category="${tx.category_id || ""}" data-action="edit">Sửa</button>
                        <button class="text-sm px-2.5 py-1 border border-red-200 text-red-500 rounded-lg hover:bg-red-50 transition-colors" data-id="${tx.id}" data-action="delete">Xóa</button>
                    </div>
                </td>
            </tr>
        `;
    },

    /**
     * Update financial summary cards (total income, expense, balance)
     * @param {Object} summary - { total_income, total_expense, balance }
     */
    updateSummary(summary) {
        const incomeEl  = document.getElementById("summary-income");
        const expenseEl = document.getElementById("summary-expense");
        const balanceEl = document.getElementById("summary-balance");

        if (incomeEl)  incomeEl.textContent  = Utils.formatMoney(summary.total_income);
        if (expenseEl) expenseEl.textContent = Utils.formatMoney(summary.total_expense);
        if (balanceEl) balanceEl.textContent = Utils.formatMoney(summary.balance);
    }
};