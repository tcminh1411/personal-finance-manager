/**
 * Validation Module
 * Handles all form input validations and user feedback
 */
const Validation = {
  /**
   * Initialize validation module
   * Sets up real-time validation and input constraints
   */
  init() {
    this.setupAmountInput();
    this.setupDateInput();
    this.setupTouchedValidation();
    this.setupCategoryFilter();
    this.setupCategoryToTypeSync();
  },

  /**
   * Setup amount input validation
   * Prevents negative numbers and invalid characters
   */
  setupAmountInput() {
    const amountInput = document.getElementById("amount");
    if (!amountInput) return;

    // Prevent invalid key presses
    amountInput.addEventListener("keydown", (e) => {
      if (["-", "+", "e", "E"].includes(e.key)) {
        e.preventDefault();
      }
    });

    // Validate pasted content
    amountInput.addEventListener("paste", () => {
      setTimeout(() => {
        const value = Number.parseFloat(amountInput.value);

        if (Number.isNaN(value) || value < 0) {
          amountInput.value = "";
          Utils.showNotification("Vui lòng nhập số dương!", "error");
        }
      }, 10);
    });

    // Auto-correct negative numbers
    amountInput.addEventListener("blur", () => {
      const value = Number.parseFloat(amountInput.value);

      if (!Number.isNaN(value) && value < 0) {
        amountInput.value = Math.abs(value);
        Utils.showNotification("Đã tự động chuyển thành số dương", "success");
      }
    });
  },

  /**
   * Setup date input validation
   * Prevents future dates
   */
  setupDateInput() {
    const dateInput = document.getElementById("date");
    if (!dateInput) return;

    dateInput.setAttribute("max", Utils.getTodayISO());

    dateInput.addEventListener("change", () => {
      if (Utils.isFutureDate(dateInput.value)) {
        Utils.showNotification("Không được chọn ngày tương lai!", "error");
        dateInput.value = Utils.getTodayISO();
      }
    });
  },

  /**
   * Setup validation feedback on blur/change
   * Adds "touched" class for CSS validation styles
   */
  setupTouchedValidation() {
    const inputs = document.querySelectorAll(
      "#transactionForm input, #transactionForm select"
    );

    inputs.forEach((input) => {
      input.addEventListener("blur", () => input.classList.add("touched"));
      input.addEventListener("change", () => input.classList.add("touched"));
    });
  },

  /**
   * Setup category filtering based on transaction type
   * Only shows categories matching selected type (income/expense)
   */
  setupCategoryFilter() {
    const typeSelect = document.getElementById("type");
    const categorySelect = document.getElementById("category");

    if (!typeSelect || !categorySelect) return;

    const allOptions = Array.from(categorySelect.options);

    typeSelect.addEventListener("change", () => {
      const selectedType = typeSelect.value;
      const currentCategory = categorySelect.value;

      // Clear and rebuild category options
      categorySelect.innerHTML = "";
      categorySelect.appendChild(allOptions[0].cloneNode(true));

      allOptions.slice(1).forEach((option) => {
        if (!selectedType || option.dataset.type === selectedType) {
          categorySelect.appendChild(option.cloneNode(true));
        }
      });

      // Try to restore previously selected category
      if (currentCategory) {
        const restored = categorySelect.querySelector(
          `option[value="${currentCategory}"]`
        );
        if (restored) {
          categorySelect.value = currentCategory;
        }
      }
    });
  },

  /**
   * Setup automatic type sync when category is selected
   * Auto-fills transaction type based on category's type
   */
  setupCategoryToTypeSync() {
    const typeSelect = document.getElementById("type");
    const categorySelect = document.getElementById("category");

    if (!typeSelect || !categorySelect) return;

    categorySelect.addEventListener("change", () => {
      const option = categorySelect.options[categorySelect.selectedIndex];

      if (!option.value || !option.dataset.type) return;

      if (typeSelect.value !== option.dataset.type) {
        typeSelect.value = option.dataset.type;

        // Visual feedback for auto-fill
        typeSelect.classList.add("auto-filled");
        setTimeout(() => {
          typeSelect.classList.remove("auto-filled");
        }, 500);

        // Trigger change event
        typeSelect.dispatchEvent(new Event("change"));

        // Restore category selection
        setTimeout(() => {
          categorySelect.value = option.value;
        }, 50);
      }
    });
  },

  /**
   * Validate entire form and return results
   * @returns {Object} Validation result
   * @returns {boolean} result.valid - Whether form is valid
   * @returns {Array<string>} result.errors - Array of error messages
   * @returns {Object} result.data - Validated and cleaned form data
   */
  validateForm() {
    const errors = [];
    const getEl = (id) => document.getElementById(id);

    const addError = (msg, el) => {
      errors.push(msg);
      if (errors.length === 1 && el) {
        getEl(el)?.focus();
      }
    };

    // Validate amount
    const rawAmount = getEl("amount").value;
    const cleanAmount = rawAmount.replace(/[^\d.-]/g, "");
    const amount = Number.parseFloat(cleanAmount);

    if (Number.isNaN(amount) || amount <= 0) {
      addError("Số tiền phải lớn hơn 0!", "amount");
    }

    // Validate type
    const type = getEl("type").value;
    if (!type) {
      addError("Vui lòng chọn loại giao dịch!", "type");
    }

    // Validate description
    const description = getEl("description").value.trim();

    if (!description) {
      addError("Vui lòng nhập mô tả!", "description");
    } else if (description.length < 3) {
      addError("Mô tả phải có ít nhất 3 ký tự!", "description");
    } else if (description.length > 255) {
      addError("Mô tả không được quá 255 ký tự!", "description");
    }

    // Validate date
    const date = getEl("date").value;

    if (!date) {
      addError("Vui lòng chọn ngày!", "date");
    } else if (Utils.isFutureDate(date)) {
      addError("Không được chọn ngày tương lai!", "date");
      getEl("date").value = Utils.getTodayISO();
    }

    return {
      valid: errors.length === 0,
      errors,
      data: {
        amount: Math.abs(amount),
        type,
        description,
        date,
        category_id: getEl("category")?.value || null,
      },
    };
  },
};
