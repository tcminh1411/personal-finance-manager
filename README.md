# 💰 Personal Finance Manager

Một ứng dụng quản lý tài chính cá nhân hiện đại, giúp bạn theo dõi dòng tiền, kiểm soát chi tiêu và quản lý ngân sách hiệu quả.
Phiên bản hiện tại: **v1.1 - Backend Integrated**, tích hợp cơ sở dữ liệu MySQL và xử lý logic bằng PHP.git add .

![Ảnh Demo Dự Án](assets/images/demo/full%20page.png)

## ✨ Tính năng chính

- **📝 CRUD Giao dịch:** Thêm, Xem, Sửa, Xóa các khoản Thu/Chi (Dữ liệu đồng bộ Database).
- **📊 Thống kê:** Tự động tính toán Tổng thu, Tổng chi và Số dư hiện tại từ dữ liệu thực.
- **🔍 Bộ lọc & Sắp xếp:**
  - Lọc giao dịch theo Ngày hoặc Loại (Thu/Chi).
  - Sắp xếp dữ liệu trực quan ngay trên bảng.
- **📱 Giao diện Responsive:** Hiển thị tốt trên cả Máy tính, Tablet và Điện thoại.
- **🗄️ Lưu trữ bền vững:** Sử dụng MySQL Database, đảm bảo an toàn dữ liệu, không bị mất khi tải lại trang hay xóa cache.

## 🛠 Công nghệ sử dụng

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla ES6).
- **Backend:** PHP 8.x (PDO - PHP Data Objects).
- **Database:** MySQL.
- **Tools:** Git, VS Code, XAMPP/Laragon.

## 🚀 Hướng dẫn cài đặt & Chạy

Dự án yêu cầu Web Server hỗ trợ PHP và MySQL (khuyên dùng XAMPP).

1. **Chuẩn bị môi trường:**

   - Cài đặt XAMPP (hoặc WAMP/Laragon).
   - Khởi động **Apache** và **MySQL**.

2. **Cài đặt Database:**

   - Mở phpMyAdmin (thường là `http://localhost/phpmyadmin`).
   - Tạo database mới tên: `finance_db`.
   - Import file `001_init.sql` (nằm trong thư mục gốc hoặc `database/`) vào database vừa tạo.

3. **Cấu hình kết nối:**

   - Mở file `config/database.php`.
   - Kiểm tra thông tin `$host`, `$username`, `$password`, `$db_name` cho khớp với máy bạn.

4. **Chạy ứng dụng:**
   - Copy thư mục dự án vào `C:/xampp/htdocs/`.
   - Mở trình duyệt truy cập: `http://localhost/personal-finance-manager`.

## 📅 Lộ trình phát triển

- [x] **Tuần 1:** Hoàn thiện UI/UX và Logic Frontend cơ bản.
- [x] **Tuần 2:** Kết nối Database MySQL và Backend PHP (CRUD hoàn chỉnh).
- [ ] **Tuần 3:** Nâng cao UX với AJAX (API) và Danh mục động (Dynamic Categories).
- [ ] **Tuần 4:** Bảo mật, Biểu đồ thống kê và Triển khai thực tế.

---

_Dự án thực hành Hybrid Fullstack - 2025_
