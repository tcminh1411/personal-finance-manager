/**
 * Filter Events Module
 * Handle all event bindings with optimized debounce
 */
const FilterEvents = {
  // Track last search value
  lastSearchValue: "",
  searchTimeout: null,

  init() {
    this.bindFilterButton();
    this.bindResetButton();
    this.bindSearchInput();
    this.bindFilterType();
    this.bindDateShortcuts();
    this.bindSortHeaders();
  },

  bindFilterButton() {
    const btn = document.getElementById("btnFilter");
    if (!btn) return;

    btn.addEventListener("click", () => {
      // Reset về trang 1 khi filter
      if (typeof FilterCore !== "undefined") {
        FilterCore.resetPagination();
      }
      if (typeof PaginationHandler !== "undefined") {
        PaginationHandler.resetToFirstPage();
      }
      FilterAPI.applyFilters();
    });
  },

  bindResetButton() {
    const btn = document.getElementById("btnReset");
    if (!btn) return;

    btn.addEventListener("click", () => {
      // Clear all filter inputs
      this.clearAllFilters();

      // Reset về trang 1
      if (typeof FilterCore !== "undefined") {
        FilterCore.resetPagination();
      }
      if (typeof PaginationHandler !== "undefined") {
        PaginationHandler.resetToFirstPage();
      }

      // Apply filters immediately
      FilterAPI.applyFilters();
    });
  },

  bindSearchInput() {
    const input = document.getElementById("filter-search");
    if (!input) return;

    input.addEventListener("input", () => {
      const currentValue = input.value.trim();
      if (currentValue === this.lastSearchValue) return;

      clearTimeout(this.searchTimeout);

      // Reset pagination về trang 1 khi search
      if (typeof FilterCore !== "undefined") {
        FilterCore.resetPagination();
      }
      if (typeof PaginationHandler !== "undefined") {
        PaginationHandler.resetToFirstPage();
      }

      const debounceTime = this.calculateDebounceTime(currentValue);

      this.searchTimeout = setTimeout(() => {
        this.lastSearchValue = currentValue;
        FilterAPI.applyFilters();
      }, debounceTime);
    });
  },

  calculateDebounceTime(value) {
    if (value.length === 0) return 0;
    if (value.length <= 2) return 800;
    if (value.length <= 5) return 400;
    return 200;
  },

  bindFilterType() {
    const filterType = document.getElementById("filter-type");
    if (!filterType) return;

    filterType.addEventListener("change", (e) => {
      if (typeof FilterCore !== "undefined") {
        FilterCore.filterCategoriesByType(e.target.value);
      }

      // Reset pagination khi đổi loại
      if (typeof FilterCore !== "undefined") {
        FilterCore.resetPagination();
      }
      if (typeof PaginationHandler !== "undefined") {
        PaginationHandler.resetToFirstPage();
      }

      FilterAPI.applyFilters();
    });
  },

  bindDateShortcuts() {
    const buttons = document.querySelectorAll(".btn-shortcut");
    if (!buttons.length) return;

    buttons.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        // Lấy range từ data attribute
        const range = e.target.dataset.range;
        if (!range) return;

        // Gọi hàm setDateRange
        if (
          typeof FilterUI !== "undefined" &&
          typeof FilterUI.setDateRange === "function"
        ) {
          FilterUI.setDateRange(range);
        } else {
          this.setDateRangeFallback(range);
        }

        // Reset pagination về trang 1
        if (typeof FilterCore !== "undefined") {
          FilterCore.resetPagination();
        }
        if (typeof PaginationHandler !== "undefined") {
          PaginationHandler.resetToFirstPage();
        }

        // Apply filters immediately
        FilterAPI.applyFilters();
      });
    });
  },

  bindSortHeaders() {
    const headers = document.querySelectorAll(".sortable");
    if (!headers.length) return;

    headers.forEach((th) => {
      th.addEventListener("click", () => {
        const column = th.dataset.key;
        if (!column) return;

        const currentState = FilterCore.getState();

        if (currentState.sort.column === column) {
          FilterCore.setSort(
            column,
            currentState.sort.order === "ASC" ? "DESC" : "ASC"
          );
        } else {
          FilterCore.setSort(column, "DESC");
        }

        // Update UI
        headers.forEach((h) => {
          h.classList.remove("sort-asc", "sort-desc");
        });

        th.classList.add(`sort-${FilterCore.currentSort.order.toLowerCase()}`);

        // Reset pagination về trang 1 khi sort
        if (typeof FilterCore !== "undefined") {
          FilterCore.resetPagination();
        }
        if (typeof PaginationHandler !== "undefined") {
          PaginationHandler.resetToFirstPage();
        }

        FilterAPI.applyFilters();
      });
    });
  },

  // Helper function to clear all filters
  clearAllFilters() {
    // Clear input fields
    const fieldsToClear = [
      "filter-search",
      "filter-type",
      "filter-category",
      "filter-date-from",
      "filter-date-to",
    ];

    fieldsToClear.forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.value = "";
    });

    // Reset sort headers
    document.querySelectorAll(".sortable").forEach((th) => {
      th.classList.remove("sort-asc", "sort-desc");
    });

    // Reset FilterCore sort state
    if (typeof FilterCore !== "undefined") {
      FilterCore.setSort("date", "DESC");
    }
  },

  // Fallback date range function
  setDateRangeFallback(range) {
    const fromEl = document.getElementById("filter-date-from");
    const toEl = document.getElementById("filter-date-to");
    if (!fromEl || !toEl) return;

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

      case "week": {
        const startOfWeek = new Date(today);
        const dayOfWeek = today.getDay();
        const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
        startOfWeek.setDate(date - daysToMonday);

        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        fromEl.value = formatDate(startOfWeek);
        toEl.value = formatDate(endOfWeek);
        break;
      }

      case "month": {
        const startOfMonth = new Date(year, month, 1);
        const endOfMonth = new Date(year, month + 1, 0);

        fromEl.value = formatDate(startOfMonth);
        toEl.value = formatDate(endOfMonth);
        break;
      }

      default:
        // Clear dates if range not recognized
        fromEl.value = "";
        toEl.value = "";
    }
  },
};
