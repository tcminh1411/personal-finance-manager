<?php
/**
 * API Endpoint: Create Transaction
 * Handles validation and persistence of new financial records.
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
 * Controller: Transaction Creation Process
 */
try {
    // A. Extract & Sanitize Input
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? getTodayISO(); // Fallback to current system date
    $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
    $user_id = 1; // Context: Default user (Replace with Session Auth later)

    // B. Validation Logic
    $errors = [];

    // B1. Amount Validation
    $amountVal = validateAmount($amount);
    if (!$amountVal['valid']) {
        $errors[] = $amountVal['error'];
    } else {
        $amount = $amountVal['value'];
    }

    // B2. Type Validation (Income/Expense)
    $typeVal = validateType($type);
    if (!$typeVal['valid']) {
        $errors[] = $typeVal['error'];
    }

    // B3. Description & Meta
    $descVal = validateDescription($description);
    if (!$descVal['valid']) {
        $errors[] = $descVal['error'];
    } else {
        $description = $descVal['value'];
    }

    // B4. Date Consistency
    $dateVal = validateDate($date);
    if (!$dateVal['valid']) {
        $errors[] = $dateVal['error'];
    }

    // B5. Relational Integrity (Category check)
    $catVal = validateCategory($pdo, $category_id, $type);
    if (!$catVal['valid']) {
        $errors[] = $catVal['error'];
    }

    // C. Handle Validation Failures
    if (!empty($errors)) {
        // Collect all issues and throw as a single exception
        throw new InvalidArgumentException(implode(', ', $errors));
    }

    // D. Database Persistence
    $sql = "INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date, created_at)
            VALUES (:user_id, :category_id, :amount, :type, :description, :date, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':category_id' => $category_id,
        ':amount' => $amount,
        ':type' => $type,
        ':description' => $description,
        ':date' => $date
    ]);

    // E. JSON Success Response
    echo json_encode([
        'success' => true,
        'message' => 'Lưu giao dịch thành công!',
        'id' => $pdo->lastInsertId()
    ]);

} catch (Throwable $e) {
    // Error Logging & Handling
    error_log("Transaction Create Error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}