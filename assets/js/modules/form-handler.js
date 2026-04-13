/**
 * Form Handler Module
 * Handles transaction form submission (Create & Update)
 */
const FormHandler = {
  isEditMode: false,
  editId: null,
  defaultTitle: "Thêm Giao Dịch",

  /**
   * Initialize form handler
   * Sets up form event listeners
   */
  init() {
    this.setupFormSubmit();
    this.setupCancelButton();
    this.resetFormMode();
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
   * Setup cancel edit button event listener
   */
  setupCancelButton() {
    const btnCancel = document.getElementById("btnCancelEdit");
    if (!btnCancel) return;

    btnCancel.addEventListener("click", () => {
      this.resetFormMode();
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
    const originalText = btnSubmit.innerHTML;

    // Show loading state
    btnSubmit.innerHTML = "<i class=\"ri-loader-line ri-spin\"></i> Đang xử lý...";
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
        this.resetFormMode();
        setTimeout(() => window.location.reload(), 1500);
      } else {
        Utils.showNotification("Lỗi: " + data.message, "error");
        this.resetButton(btnSubmit, originalText);
      }
    } catch (error) {
      Utils.showNotification("Có lỗi kết nối server!", "error");
      this.resetButton(btnSubmit, originalText);
      if (window.location.hostname === "localhost") {
        console.error("Form submission error:", error);
      }
    }
  },

  /**
   * Switch form into edit mode and prefill values
   * @param {number|string} id
   * @param {HTMLTableRowElement} row
   * @param {number|string|null} categoryId
   */
  setEditMode(id, row, categoryId) {
    const formHeader = document.querySelector("#addForm h2");
    if (formHeader) {
      formHeader.textContent = "Sửa Giao Dịch";
    }

    this.isEditMode = true;
    this.editId = id;

    this.fillFormForEdit(id, row, categoryId);

    const btnSubmit = document.querySelector("#transactionForm button[type='submit']");
    if (btnSubmit) {
      btnSubmit.innerHTML = "<i class=\"ri-save-line\"></i> Cập nhật";
      btnSubmit.classList.add("btn-warning");
    }

    const btnCancel = document.getElementById("btnCancelEdit");
    if (btnCancel) {
      btnCancel.classList.remove("hidden");
    }

    setTimeout(() => {
      const addForm = document.getElementById("addForm");
      if (addForm && addForm.offsetParent !== null) {
        addForm.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    }, 50);
  },

  /**
   * Fill form with transaction data for editing
   */
  fillFormForEdit(id, row, categoryId) {
    const cells = row.cells;

    const dateRaw = cells[1].innerText.trim();
    const typeText = cells[2].innerText.trim();
    const amountRaw = cells[4].innerText.trim();
    const description = cells[5].innerText.trim();

    let dateISO = "";
    if (typeof Utils !== "undefined" && typeof Utils.convertDateToISO === "function") {
      dateISO = Utils.convertDateToISO(dateRaw);
    }

    if (!dateISO) {
      const parts = dateRaw.split("/");
      if (parts.length === 3) {
        const day = parts[0].padStart(2, "0");
        const month = parts[1].padStart(2, "0");
        const year = parts[2];
        dateISO = `${year}-${month}-${day}`;
      } else if (typeof Utils !== "undefined" && typeof Utils.getTodayISO === "function") {
        dateISO = Utils.getTodayISO();
      } else {
        const now = new Date();
        dateISO = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;
      }
    }

    document.getElementById("transaction_id").value = id;
    document.getElementById("amount").value = Utils.parseMoney(amountRaw);

    const isIncome = typeText.includes("Thu") || typeText.includes("income");
    document.getElementById("type").value = isIncome ? "income" : "expense";

    document.getElementById("category").value = categoryId || "";
    document.getElementById("type").dispatchEvent(new Event("change"));

    setTimeout(() => {
      document.getElementById("category").value = categoryId || "";
    }, 50);

    document.getElementById("description").value = description;
    document.getElementById("date").value = dateISO;
  },

  /**
   * Reset form back to default (add) mode
   */
  resetFormMode() {
    this.isEditMode = false;
    this.editId = null;

    const formHeader = document.querySelector("#addForm h2");
    if (formHeader) {
      formHeader.textContent = this.defaultTitle;
    }

    const form = document.getElementById("transactionForm");
    if (form) {
      form.reset();
      const defaultDate =
        typeof Utils !== "undefined" && typeof Utils.getTodayISO === "function"
          ? Utils.getTodayISO()
          : new Date().toISOString().slice(0, 10);
      const dateInput = document.getElementById("date");
      if (dateInput) dateInput.value = defaultDate;
    }

    const btnSubmit = document.querySelector("#transactionForm button[type='submit']");
    if (btnSubmit) {
      btnSubmit.innerHTML = "<i class=\"ri-add-line\"></i> Thêm";
      btnSubmit.disabled = false;
      btnSubmit.classList.remove("btn-warning");
    }

    const btnCancel = document.getElementById("btnCancelEdit");
    if (btnCancel) {
      btnCancel.classList.add("hidden");
    }
  },

  /**
   * Reset button to original state
   * @param {HTMLButtonElement} button - Button element to reset
   * @param {string} originalText - Original button text
   */
  resetButton(button, originalText) {
    button.innerHTML = originalText;
    button.disabled = false;
  },
};
