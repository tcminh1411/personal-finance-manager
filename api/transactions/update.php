<?php
/**
 * API Endpoint: Update Transaction
 * Handles modification of existing records with full validation.
 */

header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/helpers.php';

// 1. Validate Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

/**
 * Controller: Transaction Update Process
 */
try {
    // A. Extract & Sanitize Input
    $id = $_POST['id'] ?? null;
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? getTodayISO();
    $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;

    // B. Identification Validation
    if (!$id) {
        throw new InvalidArgumentException("Thiếu ID giao dịch");
    }

    // C. Data Validation Logic
    $errors = [];

    // C1. Amount Validation
    $amountVal = validateAmount($amount);
    if (!$amountVal['valid']) {
        $errors[] = $amountVal['error'];
    } else {
        $amount = $amountVal['value'];
    }

    // C2. Type & Category Integrity
    $typeVal = validateType($type);
    if (!$typeVal['valid']) {
        $errors[] = $typeVal['error'];
    }

    $catVal = validateCategory($pdo, $category_id, $type);
    if (!$catVal['valid']) {
        $errors[] = $catVal['error'];
    }

    // C3. Content & Date Validation
    $descVal = validateDescription($description);
    if (!$descVal['valid']) {
        $errors[] = $descVal['error'];
    } else {
        $description = $descVal['value'];
    }

    $dateVal = validateDate($date);
    if (!$dateVal['valid']) {
        $errors[] = $dateVal['error'];
    }

    // D. Handle Validation Failures
    if (!empty($errors)) {
        throw new InvalidArgumentException(implode(', ', $errors));
    }

    // E. Database Update Execution
    $sql = "UPDATE transactions
            SET amount = :amount,
                type = :type,
                description = :description,
                transaction_date = :date,
                category_id = :category_id
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':amount' => $amount,
        ':type' => $type,
        ':description' => $description,
        ':date' => $date,
        ':category_id' => $category_id
    ]);

    // F. Success Response
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật giao dịch thành công!'
    ]);

} catch (Throwable $e) {
    // Error Logging & Handling
    error_log("Transaction Update Error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}