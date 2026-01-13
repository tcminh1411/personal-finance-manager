# 💰 Personal Finance Manager

Một ứng dụng quản lý tài chính cá nhân hiện đại, giúp bạn theo dõi dòng tiền, kiểm soát chi tiêu và quản lý ngân sách hiệu quả.
Phiên bản hiện tại: **v1.2 - AJAX & Dynamic Categories**, tích hợp AJAX hoàn chỉnh với giao diện động và xử lý không tải lại trang.

![Ảnh Demo Dự Án](assets/images/demo/full%20page.png)

## ✨ Tính năng chính

- **📝 CRUD Giao dịch AJAX:** Thêm, Xem, Sửa, Xóa các khoản Thu/Chi không tải lại trang (AJAX với API endpoints).
- **📊 Thống kê động:** Tự động tính toán Tổng thu, Tổng chi và Số dư hiện tại với real-time updates.
- **🔍 Bộ lọc & Tìm kiếm nâng cao:**
  - Lọc theo Loại (Thu/Chi), Danh mục, Khoảng thời gian
  - Tìm kiếm theo mô tả với debounce 500ms
  - Sắp xếp theo cột (ngày, số tiền, mô tả)
  - Date shortcuts nhanh (Hôm nay, Tuần này, Tháng này)
- **📑 Phân trang thông minh:**
  - Hai chế độ: Phân trang truyền thống và "Tải thêm"
  - Tùy chọn số lượng hiển thị (10, 25, 50, 100)
  - Lưu cài đặt trong localStorage
- **📥 Xuất dữ liệu CSV:**
  - Xuất toàn bộ hoặc theo filter hiện tại
  - Bao gồm tổng kết (tổng thu, tổng chi, số dư)
  - Tương thích Excel với UTF-8 BOM
- **🏷️ Danh mục động:**
  - Tự động lọc danh mục theo loại giao dịch
  - Đồng bộ loại khi chọn danh mục
  - Quản lý qua API `/api/categories/list.php`
- **📱 Giao diện Responsive:** Hiển thị tốt trên cả Máy tính, Tablet và Điện thoại.
- **🔄 Trải nghiệm mượt mà:** Không tải lại trang, validation real-time, loading states.

## 🛠 Công nghệ sử dụng

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla ES6 với Modular Architecture)
- **Backend:** PHP 8.x (PDO với prepared statements)
- **Database:** MySQL 5.7+
- **API:** RESTful với JSON responses
- **Tools:** Git, VS Code, XAMPP/Laragon

## 🚀 Hướng dẫn cài đặt & Chạy

Dự án yêu cầu Web Server hỗ trợ PHP và MySQL (khuyên dùng XAMPP).

1. **Chuẩn bị môi trường:**

   - Cài đặt XAMPP (hoặc WAMP/Laragon).
   - Khởi động **Apache** và **MySQL**.

2. **Cài đặt Database:**

   - Mở phpMyAdmin (thường là `http://localhost/phpmyadmin`).
   - Tạo database mới tên: `finance_db`.
   - Import file `migrations/001_init.sql` vào database vừa tạo.

3. **Cấu hình kết nối:**

   - Mở file `config/database.php`.
   - Kiểm tra thông tin `$host`, `$username`, `$password`, `$db_name` cho khớp với máy bạn.

4. **Chạy ứng dụng:**
   - Copy thư mục dự án vào `C:/xampp/htdocs/`.
   - Mở trình duyệt truy cập: `http://localhost/personal-finance-manager`.

## 📅 Lộ trình phát triển

- [x] **Tuần 1:** Hoàn thiện UI/UX và Logic Frontend cơ bản.
- [x] **Tuần 2:** Kết nối Database MySQL và Backend PHP (CRUD hoàn chỉnh).
- [x] **Tuần 3:** Nâng cao UX với AJAX (API) và Danh mục động (Dynamic Categories).
  - ✅ AJAX cho tất cả CRUD operations
  - ✅ Filter system với debounce search
  - ✅ Pagination với 2 mode
  - ✅ CSV export với summary
  - ✅ Dynamic category filtering
  - ✅ Modular JavaScript architecture
- [ ] **Tuần 4:** Bảo mật nâng cao, Biểu đồ thống kê và Triển khai thực tế.

_Dự án thực hành Hybrid Fullstack - 2025-26_  
_Đã hoàn thành: Frontend cơ bản, Backend PHP, AJAX & Dynamic Features_  
_Sắp tới: Biểu đồ, Bảo mật nâng cao, Deployment_
hi
