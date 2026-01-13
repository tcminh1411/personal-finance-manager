/**
 * Personal Finance Manager - Main Entry Point
 * Author: [Your Name]
 * Description: Modular architecture for better maintainability
 */

document.addEventListener("DOMContentLoaded", () => {
  initApp();
});

function initApp() {
  // 1. Form & CRUD
  if (typeof FormHandler !== "undefined") {
    FormHandler.init();
  }

  // 2. Table Operations
  if (typeof TableHandler !== "undefined") {
    TableHandler.init();
  }

  // 3. Pagination (must be before Filter)
  if (typeof PaginationHandler !== "undefined") {
    PaginationHandler.init();
  }

  // 4. Filter & Search
  if (typeof FilterHandler !== "undefined") {
    FilterHandler.init();
  }

  // 5. Validation
  if (typeof Validation !== "undefined") {
    Validation.init();
  }

  // 6. Export CSV
  if (typeof ExportHandler !== "undefined") {
    ExportHandler.init();
  }

  // 7. Charts (if available)
  if (typeof ChartHandler !== "undefined") {
    ChartHandler.init();
  }

  // ===== FIX: Set default date to today (MUST BE LAST) =====
  setDefaultDate();
}

/**
 * Set default date to today for main form
 * Separate function to ensure it runs after all modules loaded
 */
function setDefaultDate() {
  const dateInput = document.getElementById("date");

  if (!dateInput) return;

  // Only set if empty
  if (!dateInput.value || dateInput.value === "") {
    if (
      typeof Utils !== "undefined" &&
      typeof Utils.getTodayISO === "function"
    ) {
      const today = Utils.getTodayISO();
      dateInput.value = today;
    } else {
      // Fallback if Utils not loaded
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0");
      const day = String(now.getDate()).padStart(2, "0");
      dateInput.value = `${year}-${month}-${day}`;
    }
  }
}
