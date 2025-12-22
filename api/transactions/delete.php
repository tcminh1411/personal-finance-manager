<?php
header('Content-Type: application/json');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_POST['id'] ?? $input['id'] ?? null;

    if (!$id) {
        throw new InvalidArgumentException("Thiếu ID giao dịch cần xóa");
    }

    // Delete
    $sql = "DELETE FROM transactions WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa thành công!']);
    } else {
        throw new InvalidArgumentException("Không tìm thấy giao dịch hoặc lỗi khi xóa");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

