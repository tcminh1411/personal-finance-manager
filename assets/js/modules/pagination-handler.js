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

  /**
   * Setup event listeners for pagination controls
   */
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

  /**
   * Handle pagination button clicks
   */
  handlePaginationClick(e) {
    const btn = e.target.closest("button");
    if (!btn) return;

    const actions = {
      "btn-prev": () => this.goToPage(this.currentPage - 1),
      "btn-next": () => this.goToPage(this.currentPage + 1),
      "btn-page": () => {
        const page = Number.parseInt(btn.dataset.page, 10);
        if (!Number.isNaN(page)) this.goToPage(page);
      },
    };

    const btnClass = Array.from(btn.classList).find((cls) =>
      cls.startsWith("btn-")
    );

    if (btnClass && actions[btnClass]) {
      actions[btnClass]();
    }
  },

  /**
   * Handle per page change
   */
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

  /**
   * Go to specific page
   */
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

  /**
   * Reset to first page
   */
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
    });

    controls.classList.toggle("loading", show);
  },

  /**
   * Update pagination info display
   */
  updatePaginationInfo() {
    const el = document.getElementById("pagination-info");
    if (!el) return;

    if (this.totalRows === 0) {
      el.textContent = "Không có dữ liệu";
      return;
    }

    const start = (this.currentPage - 1) * this.perPage + 1;
    const end = Math.min(this.currentPage * this.perPage, this.totalRows);

    el.innerHTML = `Hiển thị <strong>${start}-${end}</strong> / <strong>${this.totalRows}</strong>`;
  },

  /**
   * Apply pagination data from API response
   */
  applyPaginationData(paginationData) {
    this.currentPage = paginationData.current_page;
    this.perPage = paginationData.per_page;
    this.totalPages = paginationData.total_pages;
    this.totalRows = paginationData.total_rows;

    this.updatePaginationInfo();
    this.renderPaginationButtons();
  },

  /**
   * Render pagination buttons
   */
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

  /**
   * Build pagination HTML
   */
  buildSimplePagination() {
    const pages = this.getVisiblePages(5);
    const { currentPage, totalPages } = this;

    return `
            <button class="btn-prev" ${
              currentPage === 1 ? "disabled" : ""
            }>‹</button>
            ${pages
              .map(
                (page) =>
                  `<button class="btn-page ${
                    page === currentPage ? "active" : ""
                  }"
                         data-page="${page}">${page}</button>`
              )
              .join("")}
            <button class="btn-next" ${
              currentPage === totalPages ? "disabled" : ""
            }>›</button>
        `;
  },

  /**
   * Get visible page numbers
   */
  getVisiblePages(max = 5) {
    let start = Math.max(1, this.currentPage - Math.floor(max / 2));
    let end = Math.min(this.totalPages, start + max - 1);

    if (end - start < max - 1) {
      start = Math.max(1, end - max + 1);
    }

    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
  },

  /**
   * Load pagination settings from localStorage
   */
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

  /**
   * Save pagination settings to localStorage
   */
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
