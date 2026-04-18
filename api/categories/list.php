<?php

/**
 * API Endpoint: Fetch Categories
 * Supports optional filtering by type (income/expense)
 */

header('Content-Type: application/json');

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $type = $_GET['type'] ?? null;
    $allowedTypes = ['income', 'expense'];
    $params = [];

    $sql = "SELECT id, name, type FROM categories";

    if ($type && in_array($type, $allowedTypes, true)) {
        $sql .= " WHERE type = :type";
        $params[':type'] = $type;
    }

    $sql .= " ORDER BY name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch (PDOException $e) {
    // Database specific errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    // General errors
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
