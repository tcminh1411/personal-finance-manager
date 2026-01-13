/**
 * Form Handler Module
 * Handles transaction form submission (Create & Update)
 */
const FormHandler = {
  /**
   * Initialize form handler
   * Sets up form submit event listener
   */
  init() {
    this.setupFormSubmit();
  },

  /**
   * Setup form submit event listener
   */
  setupFormSubmit() {
    const form = document.getElementById("transactionForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      await this.handleSubmit();
    });
  },

  /**
   * Handle form submission for create or update
   * Validates data and sends to appropriate API endpoint
   * @async
   */
  async handleSubmit() {
    const form = document.getElementById("transactionForm");
    const btnSubmit = form.querySelector("button[type='submit']");
    const originalText = btnSubmit.textContent;

    // Show loading state
    btnSubmit.textContent = "⏳ Đang xử lý...";
    btnSubmit.disabled = true;

    try {
      // Validate form data
      const validation = Validation.validateForm();

      if (!validation.valid) {
        Utils.showNotification(validation.errors[0], "error");
        this.resetButton(btnSubmit, originalText);
        return;
      }

      // Prepare form data
      const formData = new FormData();
      const id = document.getElementById("transaction_id").value;

      formData.append("amount", validation.data.amount);
      formData.append("type", validation.data.type);
      formData.append("description", validation.data.description);
      formData.append("date", validation.data.date);

      if (validation.data.category_id) {
        formData.append("category_id", validation.data.category_id);
      }

      if (id) {
        formData.append("id", id);
      }

      // Determine API endpoint
      const url = id
        ? "api/transactions/update.php"
        : "api/transactions/save.php";

      // Send request
      const response = await fetch(url, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        Utils.showNotification(data.message, "success");
        setTimeout(() => window.location.reload(), 1500);
      } else {
        Utils.showNotification("Lỗi: " + data.message, "error");
        this.resetButton(btnSubmit, originalText);
      }
    } catch (error) {
      Utils.showNotification("Có lỗi kết nối server!", "error");
      this.resetButton(btnSubmit, originalText);
    }
  },

  /**
   * Fill form with transaction data for editing
   * @param {number|string} id - Transaction ID
   * @param {HTMLTableRowElement} row - Table row element containing data
   * @param {number|string|null} categoryId - Category ID
   */
  fillFormForEdit(id, row, categoryId) {
    const cells = row.cells;

    // Extract data from table row
    const dateRaw = cells[1].innerText.trim(); // DD/MM/YYYY format
    const typeText = cells[2].innerText.trim();
    const amountRaw = cells[4].innerText.trim();
    const description = cells[5].innerText.trim();

    // Convert date from DD/MM/YYYY to YYYY-MM-DD
    let dateISO = "";

    if (
      typeof Utils !== "undefined" &&
      typeof Utils.convertDateToISO === "function"
    ) {
      dateISO = Utils.convertDateToISO(dateRaw);
    }

    // Fallback date conversion if Utils failed
    if (!dateISO) {
      const parts = dateRaw.split("/");

      if (parts.length === 3) {
        const day = parts[0].padStart(2, "0");
        const month = parts[1].padStart(2, "0");
        const year = parts[2];
        dateISO = `${year}-${month}-${day}`;
      } else {
        dateISO = Utils.getTodayISO();
      }
    }

    // Fill form fields
    document.getElementById("transaction_id").value = id;
    document.getElementById("amount").value = Utils.parseMoney(amountRaw);

    const isIncome = typeText.includes("Thu") || typeText.includes("income");
    document.getElementById("type").value = isIncome ? "income" : "expense";

    document.getElementById("category").value = categoryId || "";

    // Trigger type change to filter categories
    document.getElementById("type").dispatchEvent(new Event("change"));

    // Re-set category after filter
    setTimeout(() => {
      document.getElementById("category").value = categoryId || "";
    }, 50);

    document.getElementById("description").value = description;
    document.getElementById("date").value = dateISO;

    // Update submit button
    const btnSubmit = document.querySelector(
      "#transactionForm button[type='submit']"
    );
    btnSubmit.innerHTML = "💾 Cập nhật";
    btnSubmit.classList.add("btn-warning");

    // Scroll to form
    document.getElementById("addForm").scrollIntoView({ behavior: "smooth" });
  },

  /**
   * Reset button to original state
   * @param {HTMLButtonElement} button - Button element to reset
   * @param {string} originalText - Original button text
   */
  resetButton(button, originalText) {
    button.textContent = originalText;
    button.disabled = false;
  },
};
