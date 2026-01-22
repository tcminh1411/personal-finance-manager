<?php
/**
 * API Endpoint: Advanced Transaction Filter
 * Handles complex filtering, multi-column sorting, and server-side pagination.
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// ===== FIX: Define constants to avoid duplication =====
define('PARAM_USER_ID', ':user_id');
define('PARAM_TYPE', ':type');
define('PARAM_CATEGORY_ID', ':category_id');
define('PARAM_SEARCH', ':search');
define('PARAM_DATE_FROM', ':date_from');
define('PARAM_DATE_TO', ':date_to');
define('PARAM_LIMIT', ':limit');
define('PARAM_OFFSET', ':offset');

/**
 * Build WHERE conditions and SQL parameters
 */
function buildFilterSql($type, $category_id, $search, $date_from, $date_to)
{
    $filters = '';
    $params = [];

    if ($type && in_array($type, ['income', 'expense'], true)) {
        $filters .= " AND t.type = " . PARAM_TYPE;
        $params[PARAM_TYPE] = $type;
    }

    if ($category_id !== null && $category_id !== '') {
        $filters .= " AND t.category_id = " . PARAM_CATEGORY_ID;
        $params[PARAM_CATEGORY_ID] = (int) $category_id;
    }

    if ($search !== null && $search !== '') {
        $filters .= " AND t.description LIKE " . PARAM_SEARCH;
        $params[PARAM_SEARCH] = '%' . $search . '%';
    }

    if ($date_from !== null && $date_from !== '') {
        $filters .= " AND DATE(t.transaction_date) >= " . PARAM_DATE_FROM;
        $params[PARAM_DATE_FROM] = $date_from;
    }

    if ($date_to !== null && $date_to !== '') {
        $filters .= " AND DATE(t.transaction_date) <= " . PARAM_DATE_TO;
        $params[PARAM_DATE_TO] = $date_to;
    }

    return ['filters' => $filters, 'params' => $params];
}

/**
 * Build safe ORDER BY clause
 */
function buildOrderByClause($sort_by, $sort_order)
{
    $allowedSortColumns = [
        'transaction_date' => 't.transaction_date',
        'amount' => 't.amount',
        'type' => 't.type',
        'category_name' => 'c.name',
        'description' => 't.description',
        'date' => 't.transaction_date'
    ];

    if ($sort_by === 'date') {
        $sort_by = 'transaction_date';
    }

    $orderColumn = $allowedSortColumns[$sort_by] ?? 't.transaction_date';
    $sort_order = ($sort_order === 'ASC') ? 'ASC' : 'DESC';

    return "ORDER BY $orderColumn $sort_order";
}

/**
 * Execute main query with pagination (FILTERED BY USER)
 */
function executeMainQuery($pdo, $filters, $params, $orderClause, $limit, $offset, $user_id)
{
    $sql = "
        SELECT t.*, c.name AS category_name
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = " . PARAM_USER_ID . "
        $filters
        $orderClause
        LIMIT " . PARAM_LIMIT . " OFFSET " . PARAM_OFFSET . "
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(PARAM_USER_ID, $user_id, PDO::PARAM_INT);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(PARAM_LIMIT, $limit, PDO::PARAM_INT);
    $stmt->bindValue(PARAM_OFFSET, $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Count total records (FILTERED BY USER)
 */
function executeCountQuery($pdo, $filters, $params, $user_id)
{
    $sql = "
        SELECT COUNT(*) AS total
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = " . PARAM_USER_ID . "
        $filters
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(PARAM_USER_ID, $user_id, PDO::PARAM_INT);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $result = $stmt->fetch();

    return (int) ($result['total'] ?? 0);
}

/**
 * Calculate income and expense summary (FILTERED BY USER)
 */
function executeSummaryQuery($pdo, $filters, $params, $user_id)
{
    $sql = "
        SELECT
            SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) AS total_income,
            SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) AS total_expense
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = " . PARAM_USER_ID . "
        $filters
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(PARAM_USER_ID, $user_id, PDO::PARAM_INT);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $result = $stmt->fetch();

    return [
        'total_income' => (float) ($result['total_income'] ?? 0),
        'total_expense' => (float) ($result['total_expense'] ?? 0)
    ];
}

/**
 * Calculate pagination metadata
 */
function calculatePagination($totalRows, $limit, $currentPage)
{
    if ($totalRows === 0) {
        return [
            'total_pages' => 0,
            'current_page' => 1,
            'has_next' => false,
            'has_prev' => false
        ];
    }

    $totalPages = (int) ceil($totalRows / $limit);

    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }

    return [
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'has_next' => $currentPage < $totalPages,
        'has_prev' => $currentPage > 1
    ];
}

/**
 * Send standardized JSON response
 */
function sendJsonResponse($success, $data = null, $summary = null, $pagination = null, $error = null)
{
    $response = ['success' => $success];

    if ($success) {
        if ($data !== null) {
            $response['data'] = $data;
        }
        if ($summary !== null) {
            $response['summary'] = $summary;
        }
        if ($pagination !== null) {
            $response['pagination'] = $pagination;
        }
    } else {
        $response['message'] = $error['message'] ?? 'An error occurred while filtering data';
        $response['error'] = $error['error'] ?? null;
        $response['trace'] = $error['trace'] ?? null;
    }

    echo json_encode($response);
    exit;
}

try {
    // Get current user ID
    $current_user_id = $_SESSION['user_id'];

    // Get request parameters
    $type = getTrimmedParam('type', null);
    $category_id = getTrimmedParam('category_id', null);
    $search = getTrimmedParam('search', null);
    $date_from = getTrimmedParam('date_from', null);
    $date_to = getTrimmedParam('date_to', null);
    $sort_by = getTrimmedParam('sort_by', 'transaction_date');
    $sort_order = strtoupper(getTrimmedParam('sort_order', 'DESC'));
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int) $_GET['limit'])) : 10;
    $offset = ($page - 1) * $limit;

    // Build filters
    $filterResult = buildFilterSql($type, $category_id, $search, $date_from, $date_to);
    $filters = $filterResult['filters'];
    $params = $filterResult['params'];

    // Build ORDER BY clause
    $orderClause = buildOrderByClause($sort_by, $sort_order);

    // Execute queries (WITH USER FILTER)
    $transactions = executeMainQuery($pdo, $filters, $params, $orderClause, $limit, $offset, $current_user_id);
    $totalRows = executeCountQuery($pdo, $filters, $params, $current_user_id);
    $summary = executeSummaryQuery($pdo, $filters, $params, $current_user_id);

    // Build pagination info
    $paginationInfo = calculatePagination($totalRows, $limit, $page);

    $pagination = [
        'current_page' => $paginationInfo['current_page'],
        'per_page' => $limit,
        'total_pages' => $paginationInfo['total_pages'],
        'total_rows' => $totalRows,
        'has_next' => $paginationInfo['has_next'],
        'has_prev' => $paginationInfo['has_prev']
    ];

    // Build response data
    $responseSummary = [
        'total_income' => $summary['total_income'],
        'total_expense' => $summary['total_expense'],
        'balance' => $summary['total_income'] - $summary['total_expense'],
        'count' => $totalRows,
        'total_count' => $totalRows  // For frontend compatibility
    ];

    sendJsonResponse(true, $transactions, $responseSummary, $pagination);

} catch (Throwable $e) {
    error_log('Filter API Error: ' . $e->getMessage());
    error_log('Error Trace: ' . $e->getTraceAsString());

    http_response_code(400);
    sendJsonResponse(false, null, null, null, [
        'message' => 'An error occurred while filtering data',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}