/**
 * Export Handler Module
 * Handles CSV export functionality with current filter state
 */
const ExportHandler = {
  /**
   * Initialize export handler
   * Binds click event to export button
   */
  init() {
    const exportBtn = document.getElementById("btnExport");
    if (exportBtn) {
      exportBtn.addEventListener("click", () => this.exportToCSV());
    }
  },

  /**
   * Export filtered transactions to CSV file
   * Includes summary (total income, expense, balance) and transaction details
   * @returns {void}
   */
  exportToCSV() {
    const btn = document.getElementById("btnExport");
    if (!btn) return;

    const originalText = btn.innerHTML;
    btn.innerHTML = "⏳ Đang xuất...";
    btn.disabled = true;

    // Build URL with current filter parameters
    const params = this.buildExportParams();

    // Trigger file download
    this.downloadFile(`api/transactions/export.php?${params.toString()}`);

    // Show success message
    this.showMessage("✅ Đã xuất file CSV thành công!", "success");

    // Restore button state after short delay
    setTimeout(() => {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }, 1000);
  },

  /**
   * Build URL parameters from current filter state
   * @returns {URLSearchParams} Query parameters for export API
   */
  buildExportParams() {
    const params = new URLSearchParams();
    // Map param keys to actual DOM element IDs (filter-category, not filter-category-id)
    const mapping = {
      search: "filter-search",
      type: "filter-type",
      category_id: "filter-category",
      date_from: "filter-date-from",
      date_to: "filter-date-to",
    };

    Object.entries(mapping).forEach(([key, elementId]) => {
      const value = document.getElementById(elementId)?.value;
      if (value) params.append(key, value);
    });

    return params;
  },

  /**
   * Trigger file download via temporary link element
   * @param {string} url - Download URL
   */
  downloadFile(url) {
    const link = document.createElement("a");
    link.href = url;
    link.download = "giao-dich.csv";
    link.style.display = "none";

    document.body.appendChild(link);
    link.click();
    link.remove();
  },

  /**
   * Display notification message to user
   * @param {string} message - Message to display
   * @param {string} type - Message type ('success' or 'error')
   */
  showMessage(message, type) {
    const messageEl =
      document.getElementById("export-message") ||
      document.getElementById("notification");

    if (!messageEl) return;

    messageEl.innerHTML = message;
    messageEl.className = type;

    // Auto-hide after 4 seconds
    setTimeout(() => {
      messageEl.innerHTML = "";
      messageEl.className = "";
    }, 4000);
  },
};
