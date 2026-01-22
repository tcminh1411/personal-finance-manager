<?php
/**
 * Analytics Summary API
 * Provides grouped data for charts and analytics (FILTERED BY USER)
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    // Get current user ID
    $current_user_id = $_SESSION['user_id'];

    $type = $_GET['type'] ?? 'expense_by_category';

    switch ($type) {
        case 'expense_by_category':
            $data = getExpenseByCategory($pdo, $current_user_id);
            break;

        case 'income_vs_expense_monthly':
            $data = getIncomeVsExpenseMonthly($pdo, $current_user_id);
            break;

        case 'top_expenses':
            $data = getTopExpenses($pdo, $current_user_id);
            break;

        default:
            throw new InvalidArgumentException('Invalid analytics type');
    }

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get expense breakdown by category (FILTERED BY USER)
 */
function getExpenseByCategory($pdo, $user_id)
{
    $sql = "SELECT
                COALESCE(c.name, 'Uncategorized') AS category_name,
                SUM(t.amount) AS total_amount,
                COUNT(*) AS transaction_count,
                ROUND(
                    (SUM(t.amount) * 100.0) / NULLIF((
                        SELECT SUM(amount)
                        FROM transactions
                        WHERE type = 'expense' AND user_id = :user_id_sub
                    ), 0),
                    2
                ) AS percentage
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.type = 'expense' AND t.user_id = :user_id
            GROUP BY t.category_id, c.name
            ORDER BY total_amount DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':user_id_sub' => $user_id
    ]);

    return $stmt->fetchAll();
}

/**
 * Get income vs expense by month (FILTERED BY USER)
 */
function getIncomeVsExpenseMonthly($pdo, $user_id)
{
    $sql = "SELECT
                DATE_FORMAT(transaction_date, '%Y-%m') AS month,
                DATE_FORMAT(transaction_date, '%b %Y') AS month_label,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense,
                SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) AS balance
            FROM transactions
            WHERE user_id = :user_id
            GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
            ORDER BY month DESC
            LIMIT 12";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    $results = $stmt->fetchAll();

    // Reverse array to show oldest first
    return array_reverse($results);
}

/**
 * Get top 5 largest expenses (FILTERED BY USER)
 */
function getTopExpenses($pdo, $user_id)
{
    $sql = "SELECT
                t.description,
                t.amount,
                COALESCE(c.name, 'Uncategorized') AS category_name,
                t.transaction_date
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE t.type = 'expense' AND t.user_id = :user_id
            ORDER BY t.amount DESC
            LIMIT 5";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);

    return $stmt->fetchAll();
}