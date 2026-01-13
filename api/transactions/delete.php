<?php
/**
 * API Endpoint: Delete Transaction
 * Supports ID input via JSON body or traditional POST data
 */

header('Content-Type: application/json');

require_once '../../config/database.php';

// 1. Enforce POST Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // 2. Extract Data (Support both JSON and Form-Data)
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? $_POST['id'] ?? null;

    if (!$id) {
        throw new InvalidArgumentException("Missing transaction ID");
    }

    // 3. Execute Delete Statement
    $sql = "DELETE FROM transactions WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    // 4. Verify Row Deletion
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Transaction deleted successfully'
        ]);
    } else {
        // ID valid format but record doesn't exist in DB
        http_response_code(404);
        throw new Exception("Transaction not found or already deleted");
    }

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    // General server or database errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}