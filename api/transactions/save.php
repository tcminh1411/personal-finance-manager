<?php
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $user_id = 1; // Tạm thời set cứng là user ID 1 (Admin)

    if ($amount <= 0) {
        throw new InvalidArgumentException("Số tiền phải lớn hơn 0");
    }
    if (!in_array($type, ['income', 'expense'])) {
        throw new InvalidArgumentException("Loại giao dịch không hợp lệ (phải là income hoặc expense)");
    }

    // Save
    $sql = "INSERT INTO transactions (user_id, amount, type, description, transaction_date, created_at)
            VALUES (:user_id, :amount, :type, :description, :date, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':amount' => $amount,
        ':type' => $type,
        ':description' => $description,
        ':date' => $date
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Lưu giao dịch thành công!',
        'id' => $pdo->lastInsertId() // Trả về ID của dòng vừa thêm
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

