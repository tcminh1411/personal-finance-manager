document.addEventListener("DOMContentLoaded", () => {
  initApp();
});

function initApp() {
  setupFormSubmit();
  setupTableActions();

  // Tự động điền ngày hôm nay nếu input trống
  const dateInput = document.getElementById("date");
  if (dateInput && !dateInput.value) {
    dateInput.value = new Date().toISOString().split("T")[0];
  }
}

// Hàm hiển thị thông báo
function showNotification(message, type = "success") {
  const notifyBox = document.getElementById("notification");
  if (!notifyBox) return;

  notifyBox.textContent = message;
  notifyBox.className = type; // Class 'success' hoặc 'error'

  // Tự động ẩn sau 3 giây
  setTimeout(() => {
    notifyBox.textContent = "";
    notifyBox.className = "";
  }, 3000);
}

// 1. XỬ LÝ SUBMIT FORM
function setupFormSubmit() {
  const form = document.getElementById("transactionForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Hiệu ứng Loading
    const btnSubmit = form.querySelector("button[type='submit']");
    const originalText = btnSubmit.textContent;
    btnSubmit.textContent = "⏳ Đang xử lý...";
    btnSubmit.disabled = true;

    try {
      const id = document.getElementById("transaction_id").value;

      // TẠO FORMDATA
      const formData = new FormData();

      // Lấy giá trị input & làm sạch số tiền
      const rawAmount = document.getElementById("amount").value;
      // [FIX] Dùng replace với cờ global (g) thay vì replaceAll
      const cleanAmount = rawAmount.replaceAll(/[^\d]/g, "");

      formData.append("amount", cleanAmount);
      formData.append("type", document.getElementById("type").value);
      formData.append(
        "description",
        document.getElementById("description").value
      );
      formData.append("date", document.getElementById("date").value);

      if (id) {
        formData.append("id", id);
      }

      // Gọi API
      const url = id
        ? "api/transactions/update.php"
        : "api/transactions/save.php";
      const response = await fetch(url, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        showNotification(data.message, "success");

        setTimeout(() => {
          // [FIX] Dùng window thay vì globalThis
          globalThis.location.reload();
        }, 1500);
      } else {
        showNotification("Lỗi: " + data.message, "error");
        btnSubmit.textContent = originalText;
        btnSubmit.disabled = false;
      }
    } catch (error) {
      console.error("Lỗi submit form:", error);
      showNotification("Có lỗi kết nối server!", "error");
      btnSubmit.textContent = originalText;
      btnSubmit.disabled = false;
    }
  });
}

// 2. XỬ LÝ CÁC NÚT TRONG BẢNG
function setupTableActions() {
  const tableBody = document.getElementById("txTableBody");
  if (!tableBody) return;

  tableBody.addEventListener("click", (e) => {
    // Dùng closest để bắt đúng nút kể cả khi bấm vào icon bên trong
    const btnDelete = e.target.closest(".btn-delete");
    const btnEdit = e.target.closest(".btn-edit");

    if (btnDelete) {
      const id = btnDelete.dataset.id;
      handleDelete(id);
    }

    if (btnEdit) {
      const id = btnEdit.dataset.id;
      const row = btnEdit.closest("tr");
      fillFormForEdit(id, row);
    }
  });
}

async function handleDelete(id) {
  if (!confirm("Bạn có chắc chắn muốn xóa không?")) return;

  try {
    const formData = new FormData();
    formData.append("id", id);

    const response = await fetch("api/transactions/delete.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      // [FIX] Dùng window
      globalThis.location.reload();
    } else {
      alert("Lỗi xóa: " + data.message);
    }
  } catch (error) {
    console.error("Lỗi xóa giao dịch:", error);
    alert("Lỗi kết nối server");
  }
}

function fillFormForEdit(id, row) {
  const cells = row.cells;
  const dateRaw = cells[1].innerText.trim();
  const typeText = cells[2].innerText.trim();
  const amountRaw = cells[3].innerText.trim();
  const description = cells[4].innerText.trim();

  document.getElementById("transaction_id").value = id;
  // [FIX] Hàm parseMoney đã sửa bên dưới
  document.getElementById("amount").value = parseMoney(amountRaw);

  const isIncome = typeText.includes("Thu") || typeText.includes("income");
  document.getElementById("type").value = isIncome ? "income" : "expense";

  document.getElementById("description").value = description;
  document.getElementById("date").value = convertDateToISO(dateRaw);

  const btnSubmit = document.querySelector(
    "#transactionForm button[type='submit']"
  );
  btnSubmit.innerHTML = "💾 Cập nhật";
  btnSubmit.style.backgroundColor = "#f39c12";

  document.getElementById("addForm").scrollIntoView({ behavior: "smooth" });
}

// --- HELPER ---
function parseMoney(str) {
  // [FIX] Dùng replace với regex global
  return str.replaceAll(/[^\d]/g, "");
}

function convertDateToISO(dateStr) {
  const parts = dateStr.split("/");
  if (parts.length === 3) {
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
  }
  return "";
}
