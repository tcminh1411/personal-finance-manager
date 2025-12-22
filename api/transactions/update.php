<?php
header('Content-Type: application/json');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $id = $_POST['id'] ?? null;
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');

    if (!$id) {
        throw new InvalidArgumentException("Thiếu ID giao dịch");
    }
    if ($amount <= 0) {
        throw new InvalidArgumentException("Số tiền không hợp lệ");
    }

    // Update
    $sql = "UPDATE transactions
            SET amount = :amount, type = :type, description = :description, transaction_date = :date
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':amount' => $amount,
        ':type' => $type,
        ':description' => $description,
        ':date' => $date
    ]);

    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

