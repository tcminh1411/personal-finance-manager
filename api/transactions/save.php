<?php

// 1. Khai báo kiểu dữ liệu trả về là JSON (Chuẩn API)
header('Content-Type: application/json');

// 2. Kết nối Database
// Lưu ý: file này nằm sâu trong api/transactions/ nên phải đi ra 2 cấp (../../) mới thấy config
require_once '../../config/database.php';
require_once '../../includes/helpers.php'; // Để dùng các hàm tiện ích sau này

// 3. Chỉ chấp nhận phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // 4. Lấy dữ liệu từ Client gửi lên
    // Dùng toán tử ?? để gán mặc định nếu không có dữ liệu
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $user_id = 1; // Tạm thời set cứng là user ID 1 (Admin)

    // 5. Validate (Kiểm tra dữ liệu) cơ bản
    if ($amount <= 0) {
        throw new InvalidArgumentException("Số tiền phải lớn hơn 0");
    }
    if (!in_array($type, ['income', 'expense'])) {
        throw new InvalidArgumentException("Loại giao dịch không hợp lệ (phải là income hoặc expense)");
    }

    // 6. Chuẩn bị câu lệnh SQL (Prepared Statement - Chống Hack SQL Injection)
    $sql = "INSERT INTO transactions (user_id, amount, type, description, transaction_date, created_at)
            VALUES (:user_id, :amount, :type, :description, :date, NOW())";

    $stmt = $pdo->prepare($sql);

    // 7. Gán giá trị và thực thi
    $stmt->execute([
        ':user_id' => $user_id,
        ':amount' => $amount,
        ':type' => $type,
        ':description' => $description,
        ':date' => $date
    ]);

    // 8. Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => 'Lưu giao dịch thành công!',
        'id' => $pdo->lastInsertId() // Trả về ID của dòng vừa thêm
    ]);

} catch (Exception $e) {
    // 9. Xử lý lỗi nếu có
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

