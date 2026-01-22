<?php
/**
 * API Endpoint: Create Transaction
 * Handles validation and persistence of new financial records.
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
require_once '../../includes/helpers.php';

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

/**
 * Controller: Transaction Creation Process
 */
try {
    // Get current user ID from session
    $user_id = $_SESSION['user_id'];

    // Receive input data
    $amount = $_POST['amount'] ?? 0;
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? getTodayISO();
    $category_id = $_POST['category_id'] ?? null;

    // Convert empty string to NULL
    if (empty($category_id)) {
        $category_id = null;
    }

    // Validate all inputs
    $errors = [];

    // Validate amount
    $amountValidation = validateAmount($amount);
    if (!$amountValidation['valid']) {
        $errors[] = $amountValidation['error'];
    } else {
        $amount = $amountValidation['value'];
    }

    // Validate type
    $typeValidation = validateType($type);
    if (!$typeValidation['valid']) {
        $errors[] = $typeValidation['error'];
    }

    // Validate description
    $descValidation = validateDescription($description);
    if (!$descValidation['valid']) {
        $errors[] = $descValidation['error'];
    } else {
        $description = $descValidation['value'];
    }

    // Validate date
    $dateValidation = validateDate($date);
    if (!$dateValidation['valid']) {
        $errors[] = $dateValidation['error'];
    }

    // Validate category (if provided)
    $categoryValidation = validateCategory($pdo, $category_id, $type);
    if (!$categoryValidation['valid']) {
        $errors[] = $categoryValidation['error'];
    }

    // Return validation errors
    if (!empty($errors)) {
        // Collect all issues and throw as a single exception
        throw new InvalidArgumentException(implode(', ', $errors));
    }

    // Save to database with current user_id
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

    echo json_encode([
        'success' => true,
        'message' => 'Lưu giao dịch thành công!',
        'id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}