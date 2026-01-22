/**
 * Filter Core Module
 * State management and core filtering logic
 */

const FilterCore = {
  currentSort: {
    column: "date",
    order: "DESC",
  },
  currentPage: 1,
  perPage: 25,

  // FIX: Store original category options to prevent loss
  originalCategoryOptions: null,

  // Shared state with other modules
  getState() {
    return {
      sort: this.currentSort,
      page: this.currentPage,
      perPage: this.perPage,
    };
  },

  setSort(column, order) {
    this.currentSort.column = column;
    this.currentSort.order = order || "DESC";
  },

  resetPagination() {
    this.currentPage = 1;
  },

  goToPage(page) {
    this.currentPage = page;
  },

  // FIX: Improved category filter logic with backup
  filterCategoriesByType(type) {
    const catEl = document.getElementById("filter-category");
    if (!catEl) return;

    // FIX: Save original options on first run
    if (!this.originalCategoryOptions) {
      this.originalCategoryOptions = Array.from(
        catEl.querySelectorAll("option")
      ).map((opt) => opt.cloneNode(true));
    }

    // Clear current options
    catEl.innerHTML = "";

    // Re-add "Tất cả danh mục" option (always first)
    catEl.appendChild(this.originalCategoryOptions[0].cloneNode(true));

    // FIX: Filter and add matching categories
    this.originalCategoryOptions.slice(1).forEach((opt) => {
      // If no type selected, show all categories
      if (!type || type === "") {
        catEl.appendChild(opt.cloneNode(true));
      }
      // Otherwise, only show categories matching the selected type
      else if (opt.dataset.type === type) {
        catEl.appendChild(opt.cloneNode(true));
      }
    });

    // FIX: If current selection is now invalid, reset to "Tất cả"
    const currentValue = catEl.value;
    const stillExists = Array.from(catEl.options).some(
      (opt) => opt.value === currentValue
    );

    if (!stillExists) {
      catEl.value = "";
    }
  },
};
