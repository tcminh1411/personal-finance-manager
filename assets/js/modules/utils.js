/**
 * Utility Functions Module
 * Provides reusable helper functions across the application
 */
const Utils = {
  /**
   * Display notification message to user
   * Auto-hides after 3 seconds
   * @param {string} message - Message to display
   * @param {string} type - Message type ('success' or 'error')
   */
  showNotification(message, type = "success") {
    const notifyBox = document.getElementById("notification");
    if (!notifyBox) return;

    notifyBox.textContent = message;
    notifyBox.className = type;

    setTimeout(() => {
      notifyBox.textContent = "";
      notifyBox.className = "";
    }, 3000);
  },

  /**
   * Format number to Vietnamese currency format
   * @param {number} amount - Amount to format
   * @returns {string} Formatted money string (e.g., "1.000.000 Đ")
   */
  formatMoney(amount) {
    return `${new Intl.NumberFormat("vi-VN").format(amount)} Đ`;
  },

  /**
   * Parse money string to clean number
   * Removes all non-digit characters except decimal point
   * @param {string} str - Money string to parse
   * @returns {string} Clean number string
   */
  parseMoney(str) {
    return str.replace(/[^\d]/g, "");
  },

  /**
   * Escape HTML special characters to prevent XSS
   * @param {string} text - Text to escape
   * @returns {string} HTML-safe text
   */
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  },

  /**
   * Check if value is a valid number
   * @param {any} value - Value to check
   * @returns {boolean} True if valid number
   */
  isValidNumber(value) {
    const num = Number(value);
    return !Number.isNaN(num);
  },

  /**
   * Validate date parts (day, month, year)
   * @param {number|string} day - Day (1-31)
   * @param {number|string} month - Month (1-12)
   * @param {number|string} year - Year (4 digits)
   * @returns {boolean} True if valid date parts
   */
  isValidDateParts(day, month, year) {
    if (
      !this.isValidNumber(day) ||
      !this.isValidNumber(month) ||
      !this.isValidNumber(year)
    ) {
      return false;
    }

    const d = Number(day);
    const m = Number(month);
    const y = Number(year);

    if (d < 1 || d > 31) return false;
    if (m < 1 || m > 12) return false;
    if (String(y).length !== 4) return false;

    return true;
  },

  /**
   * Check if ISO date string is valid
   * @param {string} isoDate - Date in YYYY-MM-DD format
   * @returns {boolean} True if valid date
   */
  isValidISODate(isoDate) {
    const dateObj = new Date(isoDate);
    return !Number.isNaN(dateObj.getTime());
  },

  /**
   * Convert DD/MM/YYYY date string to ISO format (YYYY-MM-DD)
   * @param {string} dateStr - Date string in DD/MM/YYYY format
   * @returns {string} ISO date string or empty string if invalid
   */
  convertDateToISO(dateStr) {
    if (typeof dateStr !== "string") return "";

    const parts = dateStr.trim().split("/");
    if (parts.length !== 3) return "";

    const day = parts[0].padStart(2, "0");
    const month = parts[1].padStart(2, "0");
    const year = parts[2];

    if (!this.isValidDateParts(day, month, year)) return "";

    const isoDate = `${year}-${month}-${day}`;

    if (!this.isValidISODate(isoDate)) return "";

    return isoDate;
  },

  /**
   * Get today's date in ISO format (YYYY-MM-DD)
   * @returns {string} Today's date in ISO format
   */
  getTodayISO() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  },

  /**
   * Check if date is in the future
   * @param {string} dateStr - Date string in YYYY-MM-DD format
   * @returns {boolean} True if date is after today
   */
  isFutureDate(dateStr) {
    return dateStr > this.getTodayISO();
  },

  /**
   * Calculate week range (Monday to Sunday)
   * @param {Date} today - Reference date
   * @returns {Object} Week range {start: Date, end: Date}
   */
  calculateWeekRange(today) {
    const startWeek = new Date(today);
    const dayOfWeek = today.getDay();

    // Calculate days to Monday
    const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
    startWeek.setDate(today.getDate() - daysToMonday);

    const endWeek = new Date(startWeek);
    endWeek.setDate(startWeek.getDate() + 6);

    return { start: startWeek, end: endWeek };
  },

  /**
   * Calculate month range (first to last day)
   * @param {number} year - Full year
   * @param {number} month - Month (0-11)
   * @returns {Object} Month range {start: Date, end: Date}
   */
  calculateMonthRange(year, month) {
    const startMonth = new Date(year, month, 1);
    const endMonth = new Date(year, month + 1, 0);
    return { start: startMonth, end: endMonth };
  },

  /**
   * Calculate year range (Jan 1st to Dec 31st)
   * @param {number} year - Full year
   * @returns {Object} Year range {start: Date, end: Date}
   */
  calculateYearRange(year) {
    const startYear = new Date(year, 0, 1);
    const endYear = new Date(year, 11, 31);
    return { start: startYear, end: endYear };
  },

  /**
   * Get date range based on predefined period
   * @param {string} range - Range type ('today', 'week', 'month', or 'year')
   * @returns {Object} Date range {from: string, to: string} in ISO format
   */
  getDateRange(range) {
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();

    let from = "";
    let to = "";

    switch (range) {
      case "today": {
        const todayStr = this.getTodayISO();
        from = todayStr;
        to = todayStr;
        break;
      }

      case "week": {
        const weekRange = this.calculateWeekRange(today);
        from = this.formatDate(weekRange.start);
        to = this.formatDate(weekRange.end);
        break;
      }

      case "month": {
        const monthRange = this.calculateMonthRange(year, month);
        from = this.formatDate(monthRange.start);
        to = this.formatDate(monthRange.end);
        break;
      }

      case "year": {
        const yearRange = this.calculateYearRange(year);
        from = this.formatDate(yearRange.start);
        to = this.formatDate(yearRange.end);
        break;
      }

      default:
        from = "";
        to = "";
        break;
    }

    return { from, to };
  },

  /**
   * Format Date object to ISO string (YYYY-MM-DD)
   * @param {Date} date - Date object to format
   * @returns {string} Formatted date string
   */
  formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  },
};
