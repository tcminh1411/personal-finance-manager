/**
 * Pagination Handler Module
 * Handle pagination logic with filter integration
 */
const PaginationHandler = {
  currentPage: 1,
  perPage: 10,
  totalPages: 1,
  totalRows: 0,

  /**
   * Initialize pagination
   */
  init() {
    this.setupEventListeners();
    this.loadSettings();
  },

  setupEventListeners() {
    document.addEventListener("click", (e) => {
      const target = e.target;

      if (target.closest("#pagination-controls")) {
        this.handlePaginationClick(e);
      }

      if (target.id === "per-page-select") {
        this.handlePerPageChange(e);
      }
    });
  },

  handlePaginationClick(e) {
    const btn = e.target.closest("button");
    if (!btn) return;

    const action = btn.dataset.action;

    const actions = {
      first: () => this.goToPage(1),
      prev: () => this.goToPage(this.currentPage - 1),
      next: () => this.goToPage(this.currentPage + 1),
      last: () => this.goToPage(this.totalPages),
      page: () => {
        const page = Number.parseInt(btn.dataset.page, 10);
        if (!Number.isNaN(page)) this.goToPage(page);
      },
    };

    if (action && actions[action]) {
      actions[action]();
    }
  },

  handlePerPageChange(e) {
    const value = Number.parseInt(e.target.value, 10);

    if (Number.isNaN(value) || value < 1 || value > 100) return;

    this.perPage = value;
    this.resetToFirstPage();
    this.saveSettings();

    if (typeof FilterHandler !== "undefined") {
      FilterHandler.applyFilters();
    }
  },

  goToPage(page) {
    if (page < 1 || page > this.totalPages) return;

    this.showPaginationLoading(true);
    this.currentPage = page;

    if (typeof FilterHandler !== "undefined") {
      FilterHandler.applyFilters().finally(() => {
        this.showPaginationLoading(false);
      });
    }
  },

  resetToFirstPage() {
    this.currentPage = 1;

    if (typeof FilterCore !== "undefined") {
      FilterCore.resetPagination();
    }
  },

  /**
   * Show/hide pagination loading state
   */
  showPaginationLoading(show = true) {
    const controls = document.getElementById("pagination-controls");
    if (!controls) return;

    const buttons = controls.querySelectorAll("button");
    buttons.forEach((btn) => {
      btn.disabled = show;
      btn.classList.toggle("opacity-50", show);
      btn.classList.toggle("pointer-events-none", show);
    });
  },

  updatePaginationInfo() {
    const el = document.getElementById("pagination-info");
    if (!el) return;

    if (this.totalRows === 0) {
      el.textContent = "Không có dữ liệu";
      return;
    }

    const start = (this.currentPage - 1) * this.perPage + 1;
    const end = Math.min(this.currentPage * this.perPage, this.totalRows);

    el.innerHTML = `Đang hiển thị <strong>${start}-${end}</strong> / <strong>${this.totalRows}</strong>`;
  },

  applyPaginationData(paginationData) {
    this.currentPage = paginationData.current_page;
    this.perPage = paginationData.per_page;
    this.totalPages = paginationData.total_pages;
    this.totalRows = paginationData.total_rows;

    this.updatePaginationInfo();
    this.renderPaginationButtons();
  },

  renderPaginationButtons() {
    const el = document.getElementById("pagination-controls");

    if (!el || this.totalPages <= 1) {
      if (el) el.innerHTML = "";
      return;
    }

    const template = this.buildSimplePagination();

    if (el.innerHTML !== template) {
      el.innerHTML = template;
    }
  },

  buildSimplePagination() {
    const pages = this.getVisiblePages(5);
    const { currentPage, totalPages } = this;

    return `
    <button class="btn-page" 
            data-action="first" 
            ${currentPage === 1 ? "disabled" : ""}>
      <i class="ri-skip-left-line"></i>
    </button>
    <button class="btn-page" 
            data-action="prev" 
            ${currentPage === 1 ? "disabled" : ""}>
      <i class="ri-arrow-left-s-line"></i>
    </button>
    ${pages
      .map(
        (page) => `
      <button class="btn-page ${page === currentPage ? "btn-page-active" : ""}" 
        data-action="page" data-page="${page}">${page}</button>
    `,
      )
      .join("")}
    <button class="btn-page" 
            data-action="next" 
            ${currentPage === totalPages ? "disabled" : ""}>
      <i class="ri-arrow-right-s-line"></i>
    </button>
    <button class="btn-page" 
            data-action="last" 
            ${currentPage === totalPages ? "disabled" : ""}>
      <i class="ri-skip-right-line"></i>
    </button>
  `;
  },

  getVisiblePages(max = 5) {
    let start = Math.max(1, this.currentPage - Math.floor(max / 2));
    let end = Math.min(this.totalPages, start + max - 1);

    if (end - start < max - 1) {
      start = Math.max(1, end - max + 1);
    }

    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
  },

  loadSettings() {
    try {
      const saved = localStorage.getItem("pagination_per_page");
      const value = Number.parseInt(saved, 10);

      if (!Number.isNaN(value) && value > 0 && value <= 100) {
        this.perPage = value;
        const selector = document.getElementById("per-page-select");
        if (selector) selector.value = value;
      }
    } catch (error) {
      console.warn("Cannot load pagination settings:", error);
    }
  },

  saveSettings() {
    try {
      localStorage.setItem("pagination_per_page", String(this.perPage));
    } catch (error) {
      console.warn("Cannot save pagination settings:", error);
    }
  },

  /**
   * Get current pagination parameters
   */
  getParams() {
    return {
      page: this.currentPage,
      limit: this.perPage,
    };
  },
};
