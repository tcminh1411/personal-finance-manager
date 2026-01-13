<?php
/**
 * API Endpoint: Advanced Transaction Filter
 * Handles complex filtering, multi-column sorting, and server-side pagination.
 */

header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../includes/helpers.php';

// 1. Validate Method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    return;
}

/**
 * Helper: Centralized Query Executor
 * Reduces duplication across Main, Count, and Summary queries.
 */
function executeQuery($pdo, $sql, $params, $fetchMethod = 'fetchAll')
{
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        // Automatically determine data type
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key, $value, $type);
    }
    $stmt->execute();
    return $stmt->$fetchMethod(PDO::FETCH_ASSOC);
}

/**
 * Logic: Construct SQL Filter Fragment
 */
function getFilterQuery($type, $category_id, $search, $date_from, $date_to)
{
    $sql = "";
    $params = [];

    if ($type && in_array($type, ['income', 'expense'])) {
        $sql .= " AND t.type = :type";
        $params[':type'] = $type;
    }

    if (!empty($category_id)) {
        $sql .= " AND t.category_id = :category_id";
        $params[':category_id'] = (int) $category_id;
    }

    if (!empty($search)) {
        $sql .= " AND t.description LIKE :search";
        $params[':search'] = "%$search%";
    }

    if (!empty($date_from)) {
        $sql .= " AND DATE(t.transaction_date) >= :date_from";
        $params[':date_from'] = $date_from;
    }

    if (!empty($date_to)) {
        $sql .= " AND DATE(t.transaction_date) <= :date_to";
        $params[':date_to'] = $date_to;
    }

    return [$sql, $params];
}

/**
 * Logic: Generate Safe Sort Clause
 */
function getSortQuery($sort_by, $sort_order)
{
    $map = [
        'date' => 't.transaction_date',
        'amount' => 't.amount',
        'type' => 't.type',
        'category_name' => 'c.name',
        'description' => 't.description'
    ];

    $column = $map[$sort_by] ?? 't.transaction_date';
    $direction = (strtoupper($sort_order) === 'ASC') ? 'ASC' : 'DESC';

    return " ORDER BY $column $direction, t.id DESC";
}

/**
 * Controller: Main Process
 */
try {
    // A. Extract Input
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int) ($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $sort_by = $_GET['sort_by'] ?? 'date';
    $sort_order = $_GET['sort_order'] ?? 'DESC';

    // B. Build Query Parts
    list($filterSql, $params) = getFilterQuery(
        getTrimmedParam('type'),
        getTrimmedParam('category_id'),
        getTrimmedParam('search'),
        getTrimmedParam('date_from'),
        getTrimmedParam('date_to')
    );

    $sortSql = getSortQuery($sort_by, $sort_order);

    // C. Data Fetching
    // 1. Transactions List
    $dataSql = "SELECT t.*, c.name AS category_name FROM transactions t
                LEFT JOIN categories c ON t.category_id = c.id
                WHERE 1=1 $filterSql $sortSql LIMIT :limit OFFSET :offset";

    $dataParams = array_merge($params, [':limit' => $limit, ':offset' => $offset]);
    $transactions = executeQuery($pdo, $dataSql, $dataParams);

    // 2. Count Total
    $countSql = "SELECT COUNT(*) as total FROM transactions t WHERE 1=1 $filterSql";
    $countRes = executeQuery($pdo, $countSql, $params, 'fetch');
    $totalRows = (int) $countRes['total'];

    // 3. Financial Summary
    $sumSql = "SELECT
                SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) as income,
                SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) as expense
               FROM transactions t WHERE 1=1 $filterSql";
    $sumRes = executeQuery($pdo, $sumSql, $params, 'fetch');

    // D. Metadata Calculation
    $totalPages = (int) ceil($totalRows / $limit);
    $page = ($page > $totalPages && $totalPages > 0) ? $totalPages : $page;

    // E. JSON Response
    echo json_encode([
        'success' => true,
        'data' => $transactions,
        'summary' => [
            'total_income' => (float) $sumRes['income'],
            'total_expense' => (float) $sumRes['expense'],
            'balance' => (float) ($sumRes['income'] - $sumRes['expense']),
            'total_count' => $totalRows
        ],
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'per_page' => $limit,
            'total_rows' => $totalRows,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ]
    ]);

} catch (Throwable $e) {
    error_log("Filter API Error: " . $e->getMessage());
    http_response_code(isset($e->code) ? $e->code : 400);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve transaction data',
        'error' => $e->getMessage()
    ]);
}