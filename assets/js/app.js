/**
 * Personal Finance Manager - Main Entry Point
 * Description: Modular architecture for better maintainability
 */

document.addEventListener("DOMContentLoaded", () => {
  initApp();
});

function initApp() {
  if (typeof FormHandler !== "undefined") {
    FormHandler.init();
  }

  if (typeof TableHandler !== "undefined") {
    TableHandler.init();
  }

  if (typeof PaginationHandler !== "undefined") {
    PaginationHandler.init();
  }

  if (typeof FilterHandler !== "undefined") {
    FilterHandler.init();
  }

  if (typeof Validation !== "undefined") {
    Validation.init();
  }

  if (typeof ExportHandler !== "undefined") {
    ExportHandler.init();
  }

  if (typeof ChartHandler !== "undefined") {
    ChartHandler.init();
  }

  if (typeof StickyHeader !== "undefined") {
    StickyHeader.init();
  }

  // Set default date to today
  setDefaultDate();
}

/**
 * Set default date to today for main form
 * Separate function to ensure it runs after all modules loaded
 */
function setDefaultDate() {
  const dateInput = document.getElementById("date");

  if (!dateInput) return;

  if (!dateInput.value || dateInput.value === "") {
    if (
      typeof Utils !== "undefined" &&
      typeof Utils.getTodayISO === "function"
    ) {
      const today = Utils.getTodayISO();
      dateInput.value = today;
    } else {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, "0");
      const day = String(now.getDate()).padStart(2, "0");
      dateInput.value = `${year}-${month}-${day}`;
    }
  }
}
