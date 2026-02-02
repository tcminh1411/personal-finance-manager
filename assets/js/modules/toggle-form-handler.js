/**
 * Toggle Form Handler - FIXED VERSION v2
 * Handles show/hide form on mobile devices
 *
 * FIXES:
 * 1. Prevent form auto-hide when clicking inside
 * 2. Keep keyboard visible after showing form
 * 3. ZOOM FIX: Use matchMedia API to sync with CSS media queries
 */

const ToggleFormHandler = {
  isFormVisible: false, // Track form state explicitly

  init() {
    this.createToggleButton();
    this.bindEvents();
    this.handleInitialState();
    this.preventAutoHide(); // Prevent unwanted hiding
  },

  createToggleButton() {
    if (document.querySelector(".toggle-form-btn")) return;

    const button = document.createElement("button");
    button.className = "toggle-form-btn";
    button.innerHTML = "➕ Thêm Giao Dịch";
    button.type = "button";

    const addForm = document.getElementById("addForm");
    if (addForm?.parentNode) {
      addForm.parentNode.insertBefore(button, addForm);
    }
  },

  bindEvents() {
    const button = document.querySelector(".toggle-form-btn");
    if (!button) return;

    // Use mousedown instead of click to prevent conflicts
    button.addEventListener("mousedown", (e) => {
      e.preventDefault(); // Prevent focus issues
      this.toggleForm();
    });

    // Also support touch devices
    button.addEventListener(
      "touchstart",
      (e) => {
        e.preventDefault();
        this.toggleForm();
      },
      { passive: false },
    );
  },

  toggleForm() {
    const form = document.getElementById("addForm");
    const button = document.querySelector(".toggle-form-btn");

    if (!form || !button) return;

    this.isFormVisible = !this.isFormVisible;

    if (this.isFormVisible) {
      // Show form
      form.classList.add("show");
      button.innerHTML = "❌ Đóng Form";

      // Scroll smoothly
      setTimeout(() => {
        form.scrollIntoView({ behavior: "smooth", block: "start" });
      }, 100);

      // Auto-focus first input after animation
      setTimeout(() => {
        const firstInput = form.querySelector(
          'input[type="number"], input[type="text"]',
        );
        if (firstInput) {
          firstInput.focus();
        }
      }, 600); // Wait for scroll + CSS transition
    } else {
      // Hide form
      form.classList.remove("show");
      button.innerHTML = "➕ Thêm Giao Dịch";

      // Blur all inputs to close keyboard
      const inputs = form.querySelectorAll("input, select, textarea");
      inputs.forEach((input) => input.blur());
    }
  },

  // Prevent form from auto-hiding when clicking inside
  preventAutoHide() {
    const form = document.getElementById("addForm");
    if (!form) return;

    // Stop click propagation inside form
    form.addEventListener("click", (e) => {
      e.stopPropagation();
    });

    // Prevent form hide when focusing inputs
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      input.addEventListener("focus", (e) => {
        e.stopPropagation();
      });
    });
  },

  handleInitialState() {
    // ✅ FIX: Dùng matchMedia để đồng bộ với CSS media queries
    // Điều này sẽ hoạt động đúng ngay cả khi user zoom trình duyệt
    const mobileQuery = window.matchMedia("(max-width: 1000px)");

    const updateFormState = (isMobile) => {
      const form = document.getElementById("addForm");
      const button = document.querySelector(".toggle-form-btn");

      if (!form) return;

      if (isMobile) {
        // Mobile mode: form ẩn theo toggle, button hiện
        if (!this.isFormVisible) {
          form.classList.remove("show");
        }

        if (button) {
          button.style.display = "block";
          button.style.visibility = "visible";
          button.style.opacity = "1";
          // Chỉ update text khi form đang đóng
          if (!this.isFormVisible) {
            button.innerHTML = "➕ Thêm Giao Dịch";
          }
        }
      } else {
        // Desktop mode: form luôn hiện, button ẩn
        form.classList.add("show");
        this.isFormVisible = true;

        if (button) {
          button.style.display = "none";
          button.style.visibility = "hidden";
          button.style.opacity = "0";
        }
      }
    };

    // Chạy lần đầu
    updateFormState(mobileQuery.matches);

    // Lắng nghe thay đổi (bao gồm cả khi zoom)
    mobileQuery.addEventListener("change", (e) => {
      updateFormState(e.matches);
    });
  },

  hideForm() {
    const form = document.getElementById("addForm");
    const button = document.querySelector(".toggle-form-btn");

    if (!form || !this.isFormVisible) return;

    this.isFormVisible = false;
    form.classList.remove("show");

    if (button) {
      button.innerHTML = "➕ Thêm Giao Dịch";
    }

    // Close keyboard
    const inputs = form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => input.blur());
  },
};

// Initialize on DOM ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    ToggleFormHandler.init();
  });
} else {
  ToggleFormHandler.init();
}

// Export for use in other modules
window.ToggleFormHandler = ToggleFormHandler;
