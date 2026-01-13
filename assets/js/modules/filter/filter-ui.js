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
      ? '<span class="loading-spinner"></span> Đang lọc...'
      : "🔍 Lọc";

    // Add/remove loading class for styling
    if (show) {
      btnFilter.classList.add("loading");
    } else {
      btnFilter.classList.remove("loading");
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

      case "week": // Calculate Monday of current week
      {
        const startOfWeek = new Date(today);
        const dayOfWeek = today.getDay();
        const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
        startOfWeek.setDate(date - daysToMonday);

        // Calculate Sunday of current week
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        fromEl.value = formatDate(startOfWeek);
        toEl.value = formatDate(endOfWeek);
        break;
      }

      case "month": // First day of current month
      {
        const startOfMonth = new Date(year, month, 1);
        // Last day of current month
        const endOfMonth = new Date(year, month + 1, 0);

        fromEl.value = formatDate(startOfMonth);
        toEl.value = formatDate(endOfMonth);
        break;
      }

      default:
        // Clear if range not recognized
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
      infoEl.textContent = `❌ ${message || "Có lỗi xảy ra"}`;
      infoEl.className = "error";
      return;
    }

    if (count > 0) {
      infoEl.innerHTML = `✅ Tìm thấy <strong>${count}</strong> giao dịch`;
      infoEl.className = "success";
      return;
    }

    infoEl.textContent = "⚠️ Không tìm thấy giao dịch nào";
    infoEl.className = "empty";
  },

  /**
   * Clear all filter inputs
   * Now handled by FilterEvents.clearAllFilters
   */
  clearFilterInputs() {
    // This function is now handled by FilterEvents
    console.warn(
      "clearFilterInputs is deprecated. Use FilterEvents.clearAllFilters instead."
    );
  },

  /**
   * Reset sort state
   */
  resetSortState() {
    document.querySelectorAll(".sortable").forEach((th) => {
      th.classList.remove("sort-asc", "sort-desc");
    });
  },
};
