/**
 * Filter UI Module
 * Handle UI updates and rendering with loading states
 */
const FilterUI = {
  /**
   * Show or hide loading state for filter button
   * @param {boolean} show - Show loading state
   */
  showLoading(show = true) {
    const btnFilter = document.getElementById("btnFilter");
    if (!btnFilter) return;

    btnFilter.disabled = show;
    btnFilter.innerHTML = show
      ? '<i class="ri-loader-line ri-spin"></i> Đang lọc...'
      : '<i class="ri-search-line"></i> Lọc';

    // Add/remove loading class for styling
    if (show) {
      btnFilter.classList.add("opacity-50", "pointer-events-none");
    } else {
      btnFilter.classList.remove("opacity-50", "pointer-events-none");
    }
  },

  /**
   * Get value from input element
   * @param {string} id - Element ID
   * @returns {string} Value
   */
  getValue(id) {
    const element = document.getElementById(id);
    if (!element) return "";
    return element.value.trim();
  },

  /**
   * Set date range with validation
   * Improved to handle both Utils and fallback
   */
  setDateRange(range) {
    const fromEl = document.getElementById("filter-date-from");
    const toEl = document.getElementById("filter-date-to");
    if (!fromEl || !toEl) return;

    // Try to use Utils.getDateRange if available
    if (
      typeof Utils !== "undefined" &&
      typeof Utils.getDateRange === "function"
    ) {
      const dateRange = Utils.getDateRange(range);
      if (dateRange?.from && dateRange.to) {
        fromEl.value = dateRange.from;
        toEl.value = dateRange.to;
        return;
      }
    }

    // Fallback calculation
    this.setDateRangeSimple(range, fromEl, toEl);
  },

  /**
   * Simple date range calculation (fallback)
   */
  setDateRangeSimple(range, fromEl, toEl) {
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();
    const date = today.getDate();

    const formatDate = (dateObj) => {
      const y = dateObj.getFullYear();
      const m = String(dateObj.getMonth() + 1).padStart(2, "0");
      const d = String(dateObj.getDate()).padStart(2, "0");
      return `${y}-${m}-${d}`;
    };

    switch (range) {
      case "today":
        fromEl.value = formatDate(today);
        toEl.value = formatDate(today);
        break;

      case "yesterday": {
        const yesterday = new Date(today);
        yesterday.setDate(date - 1);
        fromEl.value = formatDate(yesterday);
        toEl.value = formatDate(yesterday);
        break;
      }

      case "week":
        // ... giữ nguyên code cũ
        break;

      case "month":
        // ... giữ nguyên code cũ
        break;

      case "year": {
        const startOfYear = new Date(year, 0, 1);
        const endOfYear = new Date(year, 11, 31);
        fromEl.value = formatDate(startOfYear);
        toEl.value = formatDate(endOfYear);
        break;
      }

      default:
        fromEl.value = "";
        toEl.value = "";
    }
  },

  /**
   * Update filter info display
   */
  updateFilterInfo(count, status = "success", message = "") {
    const infoEl = document.getElementById("filter-info");
    if (!infoEl) return;

    infoEl.className = "";

    if (status === "error") {
      infoEl.innerHTML = `<i class="ri-error-warning-line"></i> ${message || "Có lỗi xảy ra"}`;
      infoEl.className = "filter-info-error";
      return;
    }

    if (count > 0) {
      infoEl.innerHTML = `<i class="ri-checkbox-circle-line"></i> Tìm thấy <strong>${count}</strong> giao dịch`;
      infoEl.className = "filter-info-success";
      return;
    }

    infoEl.innerHTML = `<i class="ri-information-line"></i> Không tìm thấy giao dịch nào`;

    infoEl.className = "filter-info-empty";
  },

  /**
   * Reset sort state
   */
  resetSortState() {
    document.querySelectorAll("[data-sortable]").forEach((th) => {
      th.classList.remove("highlight-blue");
    });
  },
};
