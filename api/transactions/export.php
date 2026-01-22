<?php
/**
 * API Endpoint: CSV Export
 * Generates a formatted CSV file of transactions with financial summaries
 */

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Start session to get user_id
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized');
}

require_once '../../config/database.php';
require_once '../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Method Not Allowed');
}

try {
    // Get current user ID
    $current_user_id = $_SESSION['user_id'];

    // Get filter parameters
    $type = $_GET['type'] ?? null;
    $category_id = $_GET['category_id'] ?? null;
    $search = $_GET['search'] ?? null;
    $date_from = $_GET['date_from'] ?? null;
    $date_to = $_GET['date_to'] ?? null;

    // Build SQL query with USER FILTER
    $sql = "SELECT t.*, c.name as category_name
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = :user_id";

    $params = [':user_id' => $current_user_id];

    // Filter by transaction type
    if ($type && in_array($type, ['income', 'expense'])) {
        $sql .= " AND t.type = :type";
        $params[':type'] = $type;
    }

    // Filter by category
    if ($category_id) {
        $sql .= " AND t.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }

    // Filter by search keyword
    if ($search) {
        $sql .= " AND t.description LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    // Filter by date range
    if ($date_from) {
        $sql .= " AND t.transaction_date >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if ($date_to) {
        $sql .= " AND t.transaction_date <= :date_to";
        $params[':date_to'] = $date_to;
    }

    // Default sorting
    $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    // Calculate summary
    $totalIncome = 0;
    $totalExpense = 0;

    foreach ($transactions as $tx) {
        if ($tx['type'] === 'income') {
            $totalIncome += $tx['amount'];
        } else {
            $totalExpense += $tx['amount'];
        }
    }

    $balance = $totalIncome - $totalExpense;

    // Generate CSV file
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Set download headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="giao-dich-' . date('Y-m-d_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for Excel support
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Write summary section
    fputcsv($output, ['=== SUMMARY ===']);
    fputcsv($output, ['Total Income', formatMoney($totalIncome)]);
    fputcsv($output, ['Total Expense', formatMoney($totalExpense)]);
    fputcsv($output, ['Balance', formatMoney($balance)]);
    fputcsv($output, ['Total Transactions', count($transactions)]);
    fputcsv($output, ['Exported At', date('d/m/Y H:i:s')]);
    fputcsv($output, []);

    // Write table header
    fputcsv($output, ['No', 'Date', 'Type', 'Category', 'Amount (VND)', 'Description']);

    // Write data rows
    foreach ($transactions as $index => $tx) {
        $row = [
            $index + 1,
            date('d/m/Y', strtotime($tx['transaction_date'])),
            $tx['type'] === 'income' ? 'Income' : 'Expense',
            $tx['category_name'] ?? 'Uncategorized',
            number_format($tx['amount'], 0, ',', '.'),
            $tx['description']
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    // Clean output buffer before sending error
    if (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: text/html; charset=utf-8');
    http_response_code(500);
    die("Export failed. Please try again.");
}