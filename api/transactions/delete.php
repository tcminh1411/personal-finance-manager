<?php
/**
 * API Endpoint: Delete Transaction
 * Supports ID input via JSON body or traditional POST data
 */

header('Content-Type: application/json');

// Start session to get user_id
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // Get current user ID from session
    $current_user_id = $_SESSION['user_id'];

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_POST['id'] ?? $input['id'] ?? null;

    if (!$id) {
        throw new InvalidArgumentException("Thiếu ID giao dịch cần xóa");
    }

    // SECURITY: Check if transaction belongs to current user
    $checkSql = "SELECT user_id FROM transactions WHERE id = :id LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':id' => $id]);
    $transaction = $checkStmt->fetch();

    if (!$transaction) {
        throw new InvalidArgumentException("Giao dịch không tồn tại");
    }

    if ($transaction['user_id'] != $current_user_id) {
        http_response_code(403);
        throw new InvalidArgumentException("Bạn không có quyền xóa giao dịch này");
    }

    // Execute delete query (only if user owns it)
    $sql = "DELETE FROM transactions WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $current_user_id
    ]);

    // Verify Row Deletion
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa thành công!']);
    } else {
        throw new InvalidArgumentException("Không thể xóa giao dịch");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}