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

  // Category filter logic
  filterCategoriesByType(type) {
    const catEl = document.getElementById("filter-category");
    if (!catEl) return;

    const options = Array.from(catEl.querySelectorAll("option"));
    catEl.innerHTML = "";
    catEl.appendChild(options[0].cloneNode(true));

    options.slice(1).forEach((opt) => {
      if (!type || opt.dataset.type === type) {
        catEl.appendChild(opt.cloneNode(true));
      }
    });
  },
};
