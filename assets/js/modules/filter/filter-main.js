/**
 * Filter Handler Main Module
 * Entry point - chỉ khoảng 20-30 dòng
 */
const FilterHandler = {
  init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => {
        FilterEvents.init();
        FilterAPI.applyFilters();
      });
    } else {
      FilterEvents.init();
      FilterAPI.applyFilters();
    }
  },

  applyFilters: FilterAPI.applyFilters.bind(FilterAPI),

  resetFilters() {
    FilterUI.clearFilterInputs();
    FilterUI.resetSortState();

    // Reset cả FilterCore và PaginationHandler
    if (typeof FilterCore !== "undefined") {
      FilterCore.resetPagination();
    }
    if (typeof PaginationHandler !== "undefined") {
      PaginationHandler.resetToFirstPage();
    }

    FilterAPI.applyFilters();
  },

  goToPage(page) {
    if (typeof FilterCore !== "undefined") {
      FilterCore.goToPage(page);
    }
    FilterAPI.applyFilters();
  },

  // Các method khác nếu cần expose
  setDateRange: FilterUI.setDateRange,
  filterCategoriesByType: FilterCore.filterCategoriesByType,
};

// Export cho các module khác sử dụng (nếu cần)
window.FilterHandler = FilterHandler;
