// 1. CẤU HÌNH & BIẾN TOÀN CỤC
const STORAGE_KEY = "transactions";
let isEditing = false;
let currentEditId = null;

let sortConfig = {
  key: "date",
  direction: "desc",
};

// 2. KHỞI TẠO
document.addEventListener("DOMContentLoaded", () => {
  const today = new Date().toISOString().slice(0, 10);
  document.getElementById("date").value = today;
  document.getElementById("filter-date").value = "";
  reloadTable();
});

// 3. TỔNG QUẢN
function reloadTable() {
  let transactions = loadTransactions();
  transactions = applyFilter(transactions);
  transactions = applySort(transactions);
  renderTable(transactions);
  updateTotals(transactions);
}

// Hàm Lọc
function applyFilter(data) {
  const filterDate = document.getElementById("filter-date").value;
  const filterType = document.getElementById("filter-type").value;
  if (filterDate) data = data.filter((tx) => tx.date === filterDate);
  if (filterType) data = data.filter((tx) => tx.type === filterType);
  return data;
}

// Hàm Sắp xếp
function applySort(data) {
  const key = sortConfig.key;
  const direction = sortConfig.direction;

  return data.sort((a, b) => {
    let valueA = a[key];
    let valueB = b[key];

    // Xếp theo số (Tiền)
    if (key === "amount") {
      return direction === "asc" ? valueA - valueB : valueB - valueA;
    }

    // Xếp theo chữ (Ngày, Loại, Mô tả)
    if (typeof valueA === "string") {
      return direction === "asc"
        ? valueA.localeCompare(valueB)
        : valueB.localeCompare(valueA);
    }
    return 0;
  });
}

// 4. MODEL & VIEW (Lưu, Đọc, Vẽ)
function loadTransactions() {
  const data = localStorage.getItem(STORAGE_KEY);
  return data ? JSON.parse(data) : [];
}

function saveTransactions(transactions) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(transactions));
}

function showNotification(message, type = "success") {
  const notifyBox = document.getElementById("notification");
  notifyBox.textContent = message;
  notifyBox.className = type;
  setTimeout(() => {
    notifyBox.textContent = "";
    notifyBox.className = "";
  }, 3000);
}

function updateTotals(transactions) {
  let income = 0,
    expense = 0;
  transactions.forEach((tx) => {
    if (tx.type === "income") income += tx.amount;
    else expense += tx.amount;
  });
  const fmt = (n) => n.toLocaleString("vi-VN") + " Đ";
  document.getElementById("total-income").textContent = fmt(income);
  document.getElementById("total-expense").textContent = fmt(expense);
  document.getElementById("balance").textContent = fmt(income - expense);
}

function renderTable(transactions) {
  const tbody = document.getElementById("txTableBody");
  const emptyState = document.getElementById("emptyState");
  tbody.innerHTML = "";

  if (transactions.length === 0) {
    emptyState.style.display = "block";
    return;
  } else {
    emptyState.style.display = "none";
  }

  transactions.forEach((tx, index) => {
    const row = document.createElement("tr");
    const typeClass = tx.type === "income" ? "success" : "error";
    const typeLabel = tx.type === "income" ? "Thu" : "Chi";

    row.innerHTML = `
            <td>${index + 1}</td> <td>${tx.date}</td>
            <td class="${typeClass}" style="font-weight:bold">${typeLabel}</td>
            <td>${tx.amount.toLocaleString("vi-VN")} Đ</td>
            <td>${tx.description}</td>
            <td>
                <button class="btn-edit" data-id="${tx.id}">Sửa</button>
                <button class="btn-delete" data-id="${
                  tx.id
                }" style="color:red">Xóa</button>
            </td>
        `;
    tbody.appendChild(row);
  });
}

// 5. CONTROLLER (Sự kiện)

document.querySelectorAll("th.sortable").forEach((header) => {
  header.addEventListener("click", () => {
    const key = header.dataset.key;
    if (sortConfig.key === key) {
      sortConfig.direction = sortConfig.direction === "asc" ? "desc" : "asc";
    } else {
      sortConfig.key = key;
      sortConfig.direction = "asc";
    }
    reloadTable();
  });
});

// Submit Form
const form = document.getElementById("transactionForm");
form.addEventListener("submit", (e) => {
  e.preventDefault();
  const amount = document.getElementById("amount").value;
  const type = document.getElementById("type").value;
  const desc = document.getElementById("description").value;
  const date = document.getElementById("date").value;

  if (Number(amount) <= 0)
    return showNotification("Số tiền phải lớn hơn 0", "error");
  if (!type) return showNotification("Hãy chọn loại", "error");
  if (!desc.trim()) return showNotification("Hãy nhập mô tả", "error");

  const transactions = loadTransactions();

  if (isEditing) {
    const index = transactions.findIndex((t) => t.id === currentEditId);
    if (index !== -1) {
      transactions[index] = {
        id: currentEditId,
        date,
        amount: Number(amount),
        type,
        description: desc,
      };
      showNotification("Cập nhật thành công!");
      isEditing = false;
      currentEditId = null;
      document.querySelector("#transactionForm button").textContent = "ADD";
    }
  } else {
    transactions.push({
      id: Date.now(),
      date,
      amount: Number(amount),
      type,
      description: desc,
    });
    showNotification("Thêm mới thành công!");
  }

  saveTransactions(transactions);
  reloadTable();
  form.reset();
  document.getElementById("date").value = new Date().toISOString().slice(0, 10);
});

// Filter & Reset
document.getElementById("btnFilter").addEventListener("click", reloadTable);
document.getElementById("btnReset").addEventListener("click", () => {
  document.getElementById("filter-date").value = "";
  document.getElementById("filter-type").value = "";
  reloadTable();
});

// Edit & Delete
document.getElementById("txTableBody").addEventListener("click", (e) => {
  if (e.target.classList.contains("btn-delete")) {
    if (confirm("Xóa giao dịch này?")) {
      const id = Number(e.target.dataset.id);
      const transactions = loadTransactions();
      const newTransactions = transactions.filter((tx) => tx.id !== id);
      saveTransactions(newTransactions);
      reloadTable();
      showNotification("Đã xóa!");
    }
  }
  if (e.target.classList.contains("btn-edit")) {
    const id = Number(e.target.dataset.id);
    const transactions = loadTransactions();
    const tx = transactions.find((t) => t.id === id);
    if (tx) {
      document.getElementById("amount").value = tx.amount;
      document.getElementById("type").value = tx.type;
      document.getElementById("description").value = tx.description;
      document.getElementById("date").value = tx.date;
      isEditing = true;
      currentEditId = id;
      document.querySelector("#transactionForm button").textContent =
        "Cập nhật";
      document.getElementById("addForm").scrollIntoView({ behavior: "smooth" });
    }
  }
});
