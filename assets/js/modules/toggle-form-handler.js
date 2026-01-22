/**
 * Toggle Form Handler
 * Handles show/hide form on mobile devices
 */

const ToggleFormHandler = {
  init() {
    this.createToggleButton();
    this.bindEvents();
    this.handleInitialState();
  },

  createToggleButton() {
    // Check if button already exists
    if (document.querySelector(".toggle-form-btn")) return;

    const button = document.createElement("button");
    button.className = "toggle-form-btn";
    button.innerHTML = "➕ Thêm Giao Dịch";
    button.type = "button";

    // Insert button before #addForm
    const addForm = document.getElementById("addForm");
    if (addForm?.parentNode) {
      addForm.parentNode.insertBefore(button, addForm);
    }
  },

  bindEvents() {
    const button = document.querySelector(".toggle-form-btn");
    if (!button) return;

    button.addEventListener("click", () => {
      this.toggleForm();
    });
  },

  toggleForm() {
    const form = document.getElementById("addForm");
    const button = document.querySelector(".toggle-form-btn");

    if (!form || !button) return;

    const isVisible = form.classList.contains("show");

    if (isVisible) {
      // Hide form
      form.classList.remove("show");
      button.innerHTML = "➕ Thêm Giao Dịch";
    } else {
      // Show form
      form.classList.add("show");
      button.innerHTML = "❌ Đóng Form";

      // Scroll to form
      form.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  },

  handleInitialState() {
    const updateFormState = () => {
      const form = document.getElementById("addForm");
      const button = document.querySelector(".toggle-form-btn");

      if (!form) return;

      // Form ẩn khi màn hình < 1000px
      if (window.innerWidth < 1000) {
        form.classList.remove("show");
        if (button) {
          button.style.display = "block";
          button.style.visibility = "visible";
          button.style.opacity = "1";
        }
      } else {
        // Desktop: form luôn hiển thị, nút ẩn hoàn toàn
        form.classList.add("show");
        if (button) {
          button.style.display = "none";
          button.style.visibility = "hidden";
          button.style.opacity = "0";
        }
      }
    };

    updateFormState();

    let resizeTimeout;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(updateFormState, 150);
    });
  },

  showForm() {
    const form = document.getElementById("addForm");
    const button = document.querySelector(".toggle-form-btn");

    if (form && !form.classList.contains("show")) {
      form.classList.add("show");
      if (button) {
        button.innerHTML = "❌ Đóng Form";
      }
      form.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  },

  hideForm() {
    const form = document.getElementById("addForm");
    const button = document.querySelector(".toggle-form-btn");

    if (form?.classList.contains("show")) {
      form.classList.remove("show");
      if (button) {
        button.innerHTML = "➕ Thêm Giao Dịch";
      }
    }
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
