-- 1. Bảng Users (Người dùng) - Tạo sơ bộ để sau này làm đăng nhập
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Sẽ lưu password đã mã hóa
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng Categories (Danh mục chi tiêu/thu nhập)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('income', 'expense') NOT NULL, -- Phân loại danh mục thuộc thu hay chi
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng Transactions (Giao dịch) - Bảng chính
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL DEFAULT 1, -- Tạm thời mặc định user là 1
    category_id INT NULL,
    amount DECIMAL(15, 0) NOT NULL, -- Tiền Việt nên để số nguyên hoặc .00
    type ENUM('income', 'expense') NOT NULL,
    description VARCHAR(255),
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Tạo khóa ngoại (liên kết) để bảo toàn dữ liệu
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Thêm dữ liệu mẫu (Seed Data)
-- Tạo 1 user mặc định
INSERT INTO users (id, username, password) VALUES (1, 'admin', '123456')
ON DUPLICATE KEY UPDATE username=username;

-- Thêm các danh mục mẫu
INSERT INTO categories (name, type) VALUES 
('Lương', 'income'),
('Thưởng', 'income'),
('Đầu tư', 'income'),
('Ăn uống', 'expense'),
('Di chuyển', 'expense'),
('Nhà cửa', 'expense'),
('Giải trí', 'expense'),
('Y tế', 'expense');

-- Thêm 1-2 giao dịch mẫu để test
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date) VALUES
(1, 1, 15000000, 'income', 'Nhận lương tháng này', CURDATE()),
(1, 4, 50000, 'expense', 'Ăn sáng phở bò', CURDATE());

-- Indexes for transactions table
CREATE INDEX idx_transactions_date ON transactions(transaction_date);
CREATE INDEX idx_transactions_type ON transactions(type);
CREATE INDEX idx_transactions_category ON transactions(category_id);
CREATE INDEX idx_transactions_created ON transactions(created_at);

-- Composite index for common filter combinations
CREATE INDEX idx_transactions_filter ON transactions(transaction_date, type, category_id);

-- Index for categories table
CREATE INDEX idx_categories_type ON categories(type);

-- ============================================
-- OPTIONAL: Add foreign key constraint if not exists
-- ============================================
ALTER TABLE transactions 
ADD CONSTRAINT fk_transactions_category 
FOREIGN KEY (category_id) 
REFERENCES categories(id) 
ON DELETE SET NULL 
ON UPDATE CASCADE;