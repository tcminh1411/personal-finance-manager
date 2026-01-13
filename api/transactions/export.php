<?php
/**
 * API Endpoint: CSV Export
 * Generates a formatted CSV file of transactions with financial summaries
 */

date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once '../../config/database.php';
require_once '../../includes/helpers.php';

// 1. Validate Request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Method Not Allowed');
}

try {
    // 2. Extract and Sanitize Filters
    $filters = [
        'type' => $_GET['type'] ?? null,
        'category_id' => $_GET['category_id'] ?? null,
        'search' => $_GET['search'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
    ];

    // 3. Construct Dynamic Query
    $sql = "SELECT t.*, c.name as category_name
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE 1=1";

    $params = [];

    if ($filters['type'] && in_array($filters['type'], ['income', 'expense'])) {
        $sql .= " AND t.type = :type";
        $params[':type'] = $filters['type'];
    }

    if ($filters['category_id']) {
        $sql .= " AND t.category_id = :category_id";
        $params[':category_id'] = $filters['category_id'];
    }

    if ($filters['search']) {
        $sql .= " AND t.description LIKE :search";
        $params[':search'] = '%' . $filters['search'] . '%';
    }

    if ($filters['date_from']) {
        $sql .= " AND t.transaction_date >= :date_from";
        $params[':date_from'] = $filters['date_from'];
    }

    if ($filters['date_to']) {
        $sql .= " AND t.transaction_date <= :date_to";
        $params[':date_to'] = $filters['date_to'];
    }

    $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Use FETCH_ASSOC for clean memory management
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Calculate Financial Summary
    $summary = [
        'income' => 0,
        'expense' => 0,
        'count' => count($transactions)
    ];

    foreach ($transactions as $tx) {
        if ($tx['type'] === 'income') {
            $summary['income'] += $tx['amount'];
        } else {
            $summary['expense'] += $tx['amount'];
        }
    }
    $balance = $summary['income'] - $summary['expense'];

    // 5. Prepare Streamed Output
    if (ob_get_level()) {
        ob_end_clean();
    }

    $filename = "transactions_export_" . date('Y-m-d_H-i') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for automatic Excel recognition of special characters
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // 6. Write CSV Content
    fputcsv($output, ['--- FINANCIAL SUMMARY ---']);
    fputcsv($output, ['Total Income', number_format($summary['income'], 0, ',', '.') . ' VND']);
    fputcsv($output, ['Total Expense', number_format($summary['expense'], 0, ',', '.') . ' VND']);
    fputcsv($output, ['Current Balance', number_format($balance, 0, ',', '.') . ' VND']);
    fputcsv($output, ['Record Count', $summary['count']]);
    fputcsv($output, ['Export Date', date('d/m/Y H:i:s')]);
    fputcsv($output, []); // Empty spacer line

    fputcsv($output, ['No.', 'Date', 'Type', 'Category', 'Amount (VND)', 'Description']);

    foreach ($transactions as $index => $tx) {
        fputcsv($output, [
            $index + 1,
            date('d/m/Y', strtotime($tx['transaction_date'])),
            ucfirst($tx['type']),
            $tx['category_name'] ?? 'Uncategorized',
            number_format($tx['amount'], 0, ',', '.'),
            $tx['description']
        ]);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    die("Export System Error: " . $e->getMessage());
}