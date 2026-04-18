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

  originalCategoryOptions: null,

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

  filterCategoriesByType(type) {
    const catEl = document.getElementById("filter-category");
    if (!catEl) return;

    if (!this.originalCategoryOptions) {
      this.originalCategoryOptions = Array.from(
        catEl.querySelectorAll("option")
      ).map((opt) => opt.cloneNode(true));
    }

    // Clear current options
    catEl.innerHTML = "";

    // Re-add "Tất cả danh mục" option (always first)
    catEl.appendChild(this.originalCategoryOptions[0].cloneNode(true));

    this.originalCategoryOptions.slice(1).forEach((opt) => {
      if (!type || type === "") {
        catEl.appendChild(opt.cloneNode(true));
      }
      else if (opt.dataset.type === type) {
        catEl.appendChild(opt.cloneNode(true));
      }
    });

    const currentValue = catEl.value;
    const stillExists = Array.from(catEl.options).some(
      (opt) => opt.value === currentValue
    );

    if (!stillExists) {
      catEl.value = "";
    }
  },
};
