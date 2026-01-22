-- Xóa các bảng cũ nếu tồn tại (thứ tự xóa quan trọng do ràng buộc khóa ngoại)
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- 1. Bảng Users (Người dùng)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng Categories (Danh mục)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng Transactions (Giao dịch)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NULL,
    amount DECIMAL(15, 0) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    description VARCHAR(255),
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu
-- 1. Tạo user admin và minh
INSERT INTO users (username, password) VALUES 
('admin', '123456'),
('minh', '141103')
ON DUPLICATE KEY UPDATE username=username;

-- 2. Thêm danh mục
INSERT INTO categories (name, type) VALUES 
('Lương', 'income'),
('Thưởng', 'income'),
('Đầu tư', 'income'),
('Khác', 'income');
('Ăn uống', 'expense'),
('Di chuyển', 'expense'),
('Tiền nhà', 'expense'),
('Hóa đơn (Điện/Nước)', 'expense'),
('Mua sắm', 'expense'),
('Giải trí', 'expense'),
('Sức khỏe', 'expense'),
('Giáo dục', 'expense'),
('Khác', 'expense');

-- 3. Thêm 100 giao dịch cho user_id = 2 (10/2025 - 10/2026)

-- Tháng 10/2025 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 18000000, 'income', 'Lương tháng 10/2025', '2025-10-05'),
(2, 5, 120000, 'expense', 'Ăn tối với bạn bè', '2025-10-08'),
(2, 6, 85000, 'expense', 'Xăng xe tháng 10', '2025-10-10'),
(2, 7, 3500000, 'expense', 'Tiền thuê nhà tháng 10', '2025-10-01'),
(2, 8, 480000, 'expense', 'Hóa đơn điện tháng 10', '2025-10-12'),
(2, 9, 550000, 'expense', 'Mua quần áo mùa thu', '2025-10-15'),
(2, 10, 320000, 'expense', 'Xem phim rạp', '2025-10-18'),
(2, 11, 280000, 'expense', 'Khám sức khỏe định kỳ', '2025-10-22');

-- Tháng 11/2025 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 18200000, 'income', 'Lương tháng 11/2025', '2025-11-05'),
(2, 5, 150000, 'expense', 'Tiệc sinh nhật bạn', '2025-11-10'),
(2, 6, 88000, 'expense', 'Xăng xe tháng 11', '2025-11-12'),
(2, 7, 3500000, 'expense', 'Tiền thuê nhà tháng 11', '2025-11-01'),
(2, 8, 520000, 'expense', 'Hóa đơn nước tháng 11', '2025-11-14'),
(2, 12, 950000, 'expense', 'Mua sách chuyên ngành', '2025-11-18'),
(2, 10, 420000, 'expense', 'Du lịch cuối tuần', '2025-11-22'),
(2, 3, 3500000, 'income', 'Lãi đầu tư chứng khoán', '2025-11-25');

-- Tháng 12/2025 (10 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 18500000, 'income', 'Lương tháng 12/2025', '2025-12-05'),
(2, 5, 180000, 'expense', 'Tiệc Giáng sinh', '2025-12-24'),
(2, 6, 92000, 'expense', 'Xăng xe tháng 12', '2025-12-10'),
(2, 7, 3500000, 'expense', 'Tiền thuê nhà tháng 12', '2025-12-01'),
(2, 8, 620000, 'expense', 'Hóa đơn điện tháng 12', '2025-12-12'),
(2, 9, 1200000, 'expense', 'Mua quà Tết', '2025-12-18'),
(2, 10, 580000, 'expense', 'Mua vé concert', '2025-12-20'),
(2, 11, 320000, 'expense', 'Mua thuốc bổ', '2025-12-22'),
(2, 2, 5000000, 'income', 'Thưởng Tết dương lịch', '2025-12-28'),
(2, 5, 110000, 'expense', 'Nhậu với đồng nghiệp', '2025-12-29');

-- Tháng 1/2026 (9 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 19000000, 'income', 'Lương tháng 01/2026', '2026-01-05'),
(2, 5, 220000, 'expense', 'Ăn Tết nguyên đán', '2026-01-28'),
(2, 6, 95000, 'expense', 'Xăng xe tháng 1', '2026-01-10'),
(2, 7, 3600000, 'expense', 'Tiền thuê nhà tháng 1', '2026-01-01'),
(2, 8, 580000, 'expense', 'Hóa đơn điện tháng 1', '2026-01-12'),
(2, 9, 2500000, 'expense', 'Mua sắm Tết', '2026-01-15'),
(2, 10, 450000, 'expense', 'Du lịch Tết', '2026-01-30'),
(2, 2, 8000000, 'income', 'Thưởng Tết âm lịch', '2026-01-25'),
(2, 13, 150000, 'expense', 'Lì xì Tết', '2026-01-29');

-- Tháng 2/2026 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 19200000, 'income', 'Lương tháng 02/2026', '2026-02-05'),
(2, 5, 130000, 'expense', 'Buffet hải sản', '2026-02-08'),
(2, 6, 90000, 'expense', 'Xăng xe tháng 2', '2026-02-10'),
(2, 7, 3600000, 'expense', 'Tiền thuê nhà tháng 2', '2026-02-01'),
(2, 8, 520000, 'expense', 'Hóa đơn điện tháng 2', '2026-02-12'),
(2, 9, 850000, 'expense', 'Mua điện thoại mới', '2026-02-18'),
(2, 10, 380000, 'expense', 'Xem ca nhạc', '2026-02-20'),
(2, 3, 4200000, 'income', 'Lãi đầu tư vàng', '2026-02-25');

-- Tháng 3/2026 (9 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 19500000, 'income', 'Lương tháng 03/2026', '2026-03-05'),
(2, 5, 115000, 'expense', 'Cafe làm việc', '2026-03-08'),
(2, 6, 88000, 'expense', 'Xăng xe tháng 3', '2026-03-10'),
(2, 7, 3600000, 'expense', 'Tiền thuê nhà tháng 3', '2026-03-01'),
(2, 8, 510000, 'expense', 'Hóa đơn nước tháng 3', '2026-03-12'),
(2, 12, 1800000, 'expense', 'Đóng học phí đại học', '2026-03-15'),
(2, 10, 520000, 'expense', 'Đi du lịch Đà Lạt', '2026-03-20'),
(2, 9, 750000, 'expense', 'Mua quà sinh nhật bạn gái', '2026-03-25'),
(2, 2, 3500000, 'income', 'Thưởng dự án thành công', '2026-03-28');

-- Tháng 4/2026 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 19800000, 'income', 'Lương tháng 04/2026', '2026-04-05'),
(2, 5, 125000, 'expense', 'Ăn trưa hàng ngày', '2026-04-08'),
(2, 6, 92000, 'expense', 'Xăng xe tháng 4', '2026-04-10'),
(2, 7, 3700000, 'expense', 'Tiền thuê nhà tháng 4', '2026-04-01'),
(2, 8, 580000, 'expense', 'Hóa đơn điện tháng 4', '2026-04-12'),
(2, 9, 1200000, 'expense', 'Mua laptop mới', '2026-04-18'),
(2, 10, 420000, 'expense', 'Chơi golf cuối tuần', '2026-04-22'),
(2, 11, 350000, 'expense', 'Khám bệnh cảm cúm', '2026-04-25');

-- Tháng 5/2026 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 20000000, 'income', 'Lương tháng 05/2026', '2026-05-05'),
(2, 5, 140000, 'expense', 'Ăn hải sản cao cấp', '2026-05-08'),
(2, 6, 95000, 'expense', 'Xăng xe tháng 5', '2026-05-10'),
(2, 7, 3700000, 'expense', 'Tiền thuê nhà tháng 5', '2026-05-01'),
(2, 8, 620000, 'expense', 'Hóa đơn điện+nước tháng 5', '2026-05-12'),
(2, 12, 1200000, 'expense', 'Mua khóa học tiếng Anh', '2026-05-15'),
(2, 10, 680000, 'expense', 'Đi resort nghỉ dưỡng', '2026-05-20'),
(2, 3, 4500000, 'income', 'Cổ tức cổ phiếu', '2026-05-25');

-- Tháng 6/2026 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 20200000, 'income', 'Lương tháng 06/2026', '2026-06-05'),
(2, 5, 135000, 'expense', 'Buffet lẩu', '2026-06-08'),
(2, 6, 98000, 'expense', 'Xăng xe tháng 6', '2026-06-10'),
(2, 7, 3700000, 'expense', 'Tiền thuê nhà tháng 6', '2026-06-01'),
(2, 8, 590000, 'expense', 'Hóa đơn internet+TV', '2026-06-12'),
(2, 9, 2200000, 'expense', 'Mua TV mới 4K', '2026-06-18'),
(2, 10, 410000, 'expense', 'Xem phim với bạn gái', '2026-06-22'),
(2, 11, 480000, 'expense', 'Khám tổng quát', '2026-06-25');

-- Tháng 7/2026 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 20500000, 'income', 'Lương tháng 07/2026', '2026-07-05'),
(2, 5, 125000, 'expense', 'Ăn tối lãng mạn', '2026-07-08'),
(2, 6, 102000, 'expense', 'Xăng xe tháng 7', '2026-07-10'),
(2, 7, 3800000, 'expense', 'Tiền thuê nhà tháng 7', '2026-07-01'),
(2, 8, 650000, 'expense', 'Hóa đơn điện tháng 7', '2026-07-12'),
(2, 9, 850000, 'expense', 'Mua đồ công nghệ', '2026-07-15'),
(2, 12, 950000, 'expense', 'Đóng học phí ngoại ngữ', '2026-07-18'),
(2, 4, 2500000, 'income', 'Tiền hoàn thuế', '2026-07-22');

-- Tháng 8/2026 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 20800000, 'income', 'Lương tháng 08/2026', '2026-08-05'),
(2, 5, 155000, 'expense', 'Tiệc sinh nhật bản thân', '2026-08-10'),
(2, 6, 105000, 'expense', 'Xăng xe tháng 8', '2026-08-12'),
(2, 7, 3800000, 'expense', 'Tiền thuê nhà tháng 8', '2026-08-01'),
(2, 8, 680000, 'expense', 'Hóa đơn điện tháng 8', '2026-08-14'),
(2, 9, 1500000, 'expense', 'Mua máy tính bảng', '2026-08-18'),
(2, 10, 750000, 'expense', 'Đi bar với bạn bè', '2026-08-22'),
(2, 3, 3800000, 'income', 'Lợi nhuận kinh doanh', '2026-08-25');

-- Tháng 9/2026 (8 giao dịch)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 21000000, 'income', 'Lương tháng 09/2026', '2026-09-05'),
(2, 5, 135000, 'expense', 'Ăn vặt cuối tuần', '2026-09-08'),
(2, 6, 108000, 'expense', 'Xăng xe tháng 9', '2026-09-10'),
(2, 7, 3800000, 'expense', 'Tiền thuê nhà tháng 9', '2026-09-01'),
(2, 8, 720000, 'expense', 'Hóa đơn điện tháng 9', '2026-09-12'),
(2, 9, 950000, 'expense', 'Mua đồ thể thao', '2026-09-15'),
(2, 10, 520000, 'expense', 'Đi du lịch biển', '2026-09-20'),
(2, 2, 4500000, 'income', 'Thưởng hiệu suất quý III', '2026-09-25');

-- Tháng 10/2026 (8 giao dịch - đủ 100)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(2, 1, 21500000, 'income', 'Lương tháng 10/2026', '2026-10-05'),
(2, 5, 145000, 'expense', 'Tiệc mừng lương tăng', '2026-10-08'),
(2, 6, 112000, 'expense', 'Xăng xe tháng 10', '2026-10-10'),
(2, 7, 3900000, 'expense', 'Tiền thuê nhà tháng 10', '2026-10-01'),
(2, 8, 750000, 'expense', 'Hóa đơn điện+nước tháng 10', '2026-10-12'),
(2, 9, 1800000, 'expense', 'Mua đồ nội thất', '2026-10-15'),
(2, 10, 680000, 'expense', 'Tiệc Halloween', '2026-10-28'),
(2, 3, 5000000, 'income', 'Lãi đầu tư bất động sản', '2026-10-30');

-- Tạo indexes để tối ưu hiệu suất truy vấn
CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_category ON transactions(category_id);
CREATE INDEX idx_transactions_created ON transactions(created_at);
CREATE INDEX idx_transactions_user ON transactions(user_id);
CREATE INDEX idx_categories_type ON categories(type);

-- Composite index cho các truy vấn phổ biến
CREATE INDEX idx_transactions_filter ON transactions(transaction_date, type, category_id, user_id);
