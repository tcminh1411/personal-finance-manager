<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // Lấy ID từ input
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_POST['id'] ?? $input['id'] ?? null;

    if (!$id) {
        // SỬA: Dùng Exception cụ thể
        throw new InvalidArgumentException("Thiếu ID giao dịch cần xóa");
    }

    // Thực hiện xóa
    $sql = "DELETE FROM transactions WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa thành công!']);
    } else {
        // SỬA: Logic lỗi runtime (chạy rồi mới thấy lỗi)
        throw new InvalidArgumentException("Không tìm thấy giao dịch hoặc lỗi khi xóa");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
// SỬA: Đã bỏ thẻ đóng PHP ở đây
