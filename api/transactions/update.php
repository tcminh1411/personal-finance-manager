<?php

/**
 * API Endpoint: Update Transaction
 * Handles modification of existing records with full validation.
 */

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';
require_once '../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $current_user_id = $_SESSION['user_id'];

    $id = $_POST['id'] ?? null;
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? getTodayISO();
    $category_id = $_POST['category_id'] ?? null;

    if (empty($category_id)) {
        $category_id = null;
    }

    if (!$id) {
        throw new InvalidArgumentException("Thiếu ID giao dịch");
    }

    // SECURITY: Check if transaction belongs to current user
    $checkSql = "SELECT user_id FROM transactions WHERE id = :id LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':id' => $id]);
    $transaction = $checkStmt->fetch();

    if (!$transaction) {
        throw new InvalidArgumentException("Giao dịch không tồn tại");
    }

    // SECURITY: Check if transaction belongs to current user
    if ($transaction['user_id'] != $current_user_id) {
        http_response_code(403);
        throw new InvalidArgumentException("Bạn không có quyền sửa giao dịch này");
    }

    $errors = [];

    $amountValidation = validateAmount($amount);
    if (!$amountValidation['valid']) {
        $errors[] = $amountValidation['error'];
    } else {
        $amount = $amountValidation['value'];
    }

    $typeValidation = validateType($type);
    if (!$typeValidation['valid']) {
        $errors[] = $typeValidation['error'];
    }

    $descValidation = validateDescription($description);
    if (!$descValidation['valid']) {
        $errors[] = $descValidation['error'];
    } else {
        $description = $descValidation['value'];
    }

    $dateValidation = validateDate($date);
    if (!$dateValidation['valid']) {
        $errors[] = $dateValidation['error'];
    }

    $categoryValidation = validateCategory($pdo, $category_id, $type);
    if (!$categoryValidation['valid']) {
        $errors[] = $categoryValidation['error'];
    }

    if (!empty($errors)) {
        throw new InvalidArgumentException(implode(', ', $errors));
    }

    $sql = "UPDATE transactions
            SET amount = :amount,
                type = :type,
                description = :description,
                transaction_date = :date,
                category_id = :category_id
            WHERE id = :id AND user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $current_user_id,
        ':amount' => $amount,
        ':type' => $type,
        ':description' => $description,
        ':date' => $date,
        ':category_id' => $category_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
