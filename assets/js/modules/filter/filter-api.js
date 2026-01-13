/**
 * Filter API Module
 * Handles API communication for filtering transactions
 */
const FilterAPI = {
  currentAbortController: null,

  /**
   * Apply current filters and fetch filtered transactions
   * Cancels any pending request before making a new one
   * @async
   * @returns {Promise<void>}
   */
  async applyFilters() {
    // Cancel previous request if exists
    if (this.currentAbortController) {
      this.currentAbortController.abort();
    }

    this.currentAbortController = new AbortController();
    const signal = this.currentAbortController.signal;
    const params = this.buildFilterParams();
    const url = `api/transactions/filter.php?${params}`;

    try {
      FilterUI.showLoading(true);

      const response = await fetch(url, { signal });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const result = await response.json();

      if (result.success) {
        this.handleFilterSuccess(result);
      } else {
        FilterUI.updateFilterInfo(
          0,
          "error",
          result.message || "Unknown error"
        );
      }
    } catch (error) {
      if (error.name !== "AbortError") {
        FilterUI.updateFilterInfo(
          0,
          "error",
          "Không thể tải dữ liệu. Vui lòng thử lại."
        );
      }
    } finally {
      FilterUI.showLoading(false);
      this.currentAbortController = null;
    }
  },

  /**
   * Build URL query parameters from current filter state
   * @returns {URLSearchParams} Query parameters for filter API
   */
  buildFilterParams() {
    const { sort } = FilterCore.getState();

    // Map frontend column names to backend column names
    const columnMapping = {
      date: "transaction_date",
      category_name: "category_name",
    };

    const sortColumn = columnMapping[sort.column] || sort.column;
    const params = new URLSearchParams();

    // Collect filter values
    const filterValues = {
      search: FilterUI.getValue("filter-search"),
      type: FilterUI.getValue("filter-type"),
      category_id: FilterUI.getValue("filter-category"),
      date_from: FilterUI.getValue("filter-date-from"),
      date_to: FilterUI.getValue("filter-date-to"),
      sort_by: sortColumn,
      sort_order: sort.order,
    };

    // Add non-empty values to params
    Object.entries(filterValues).forEach(([key, value]) => {
      if (value !== "" && value !== null && value !== undefined) {
        params.append(key, value.toString());
      }
    });

    // Add pagination params
    if (typeof PaginationHandler === "undefined") {
      const { page, perPage } = FilterCore.getState();
      params.append("page", page.toString());
      params.append("limit", perPage.toString());
    } else {
      params.append("page", PaginationHandler.currentPage.toString());
      params.append("limit", PaginationHandler.perPage.toString());
    }

    return params;
  },

  /**
   * Handle successful filter response
   * Updates table, summary, and pagination UI
   * @param {Object} result - API response data
   * @param {Array} result.data - Filtered transactions
   * @param {Object} result.summary - Summary statistics
   * @param {Object} result.pagination - Pagination info
   */
  handleFilterSuccess(result) {
    // Calculate starting index for row numbering
    let startIndex = 0;

    if (typeof PaginationHandler === "undefined") {
      const { page, perPage } = FilterCore.getState();
      startIndex = (page - 1) * perPage;
    } else {
      startIndex =
        (PaginationHandler.currentPage - 1) * PaginationHandler.perPage;
    }

    // Update table and summary if handlers exist
    if (typeof TableHandler !== "undefined") {
      if (typeof TableHandler.updateTable === "function") {
        TableHandler.updateTable(result.data, startIndex);
      }

      if (typeof TableHandler.updateSummary === "function") {
        TableHandler.updateSummary(result.summary);
      }
    }

    // Update filter info message
    FilterUI.updateFilterInfo(result.summary.total_count);

    // Update pagination UI
    if (typeof PaginationHandler !== "undefined" && result.pagination) {
      PaginationHandler.applyPaginationData(result.pagination);
    } else if (result.pagination) {
      FilterUI.updatePaginationUI(result.pagination);
    }

    // Refresh charts if available
    if (
      typeof ChartHandler !== "undefined" &&
      typeof ChartHandler.refresh === "function"
    ) {
      ChartHandler.refresh();
    }
  },
};
