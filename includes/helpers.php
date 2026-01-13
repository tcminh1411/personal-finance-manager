<?php
// 1. FORMAT & DISPLAY
function formatMoney($amount)
{
    return number_format($amount, 0, ',', '.') . ' Đ';
}

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// 2. VALIDATION HELPERS
function validateAmount($amount)
{
    $result = [
        'valid' => false,
        'value' => null,
        'error' => ''
    ];

    $amount = abs((float) $amount);

    if ($amount <= 0) {
        $result['error'] = 'Số tiền phải lớn hơn 0';
    } else {
        $result['valid'] = true;
        $result['value'] = $amount;
    }

    return $result;
}

function validateType($type)
{
    $result = [
        'valid' => false,
        'error' => ''
    ];

    if (!in_array($type, ['income', 'expense'], true)) {
        $result['error'] = 'Loại giao dịch không hợp lệ';
    } else {
        $result['valid'] = true;
    }

    return $result;
}

function validateDescription($description)
{
    $result = [
        'valid' => false,
        'value' => '',
        'error' => ''
    ];

    $description = trim($description);

    if ($description === '') {
        $result['error'] = 'Mô tả không được để trống';
    } elseif (strlen($description) < 3) {
        $result['error'] = 'Mô tả phải có ít nhất 3 ký tự';
    } elseif (strlen($description) > 255) {
        $result['error'] = 'Mô tả không được quá 255 ký tự';
    } else {
        $result['valid'] = true;
        $result['value'] = $description;
    }

    return $result;
}

function validateDate($date)
{
    $result = [
        'valid' => false,
        'error' => ''
    ];

    if ($date === '') {
        $result['error'] = 'Ngày không được để trống';
    } else {
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);

        if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
            $result['error'] = 'Định dạng ngày không hợp lệ';
        } else {
            $result['valid'] = true;
        }
    }

    return $result;
}

function validateCategory(PDO $pdo, $category_id, $type)
{
    $result = [
        'valid' => false,
        'error' => ''
    ];

    // Category optional
    if ($category_id === null || $category_id === '') {
        $result['valid'] = true;
        return $result;
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT id FROM categories
                WHERE id = :cat_id AND type = :type"
        );

        $stmt->execute([
            ':cat_id' => $category_id,
            ':type' => $type
        ]);

        if ($stmt->fetch()) {
            $result['valid'] = true;
        } else {
            $result['error'] = 'Danh mục không hợp lệ hoặc không khớp với loại giao dịch';
        }

    } catch (PDOException $e) {
        $result['error'] = 'Lỗi kiểm tra danh mục';
    }

    return $result;
}

// 3. FILTER & PARAM HELPERS
function getTrimmedParam($key, $default = null)
{
    if (!isset($_GET[$key]) || trim($_GET[$key]) === '') {
        return $default;
    }

    return trim($_GET[$key]);
}

// 4. DATE HELPERS

/**
 * Calculate week range (Monday to Sunday)
 * @param DateTime $today Today's date
 * @return array ['start' => DateTime, 'end' => DateTime]
 */
function calculateWeekRange($today)
{
    // Day of week: 1=Monday, 7=Sunday
    $dayOfWeek = (int) $today->format('N');

    // Calculate days to Monday (if Monday, daysToMonday = 0)
    $daysToMonday = $dayOfWeek - 1;

    $start = clone $today;
    $start->modify("-{$daysToMonday} days");

    $end = clone $start;
    $end->modify('+6 days');

    return ['start' => $start, 'end' => $end];
}

/**
 * Calculate month range (1st to last day of month)
 * @param int $year Full year
 * @param int $month Month (1-12)
 * @return array ['start' => DateTime, 'end' => DateTime]
 */
function calculateMonthRange($year, $month)
{
    $start = new DateTime("{$year}-{$month}-01", new DateTimeZone(TIMEZONE_VIETNAM));

    // Last day of month
    $end = new DateTime("{$year}-{$month}-" . $start->format('t'), new DateTimeZone(TIMEZONE_VIETNAM));

    return ['start' => $start, 'end' => $end];
}

/**
 * Calculate year range (Jan 1st to Dec 31st)
 * @param int $year Full year
 * @return array ['start' => DateTime, 'end' => DateTime]
 */
function calculateYearRange($year)
{
    $start = new DateTime("{$year}-01-01", new DateTimeZone(TIMEZONE_VIETNAM));
    $end = new DateTime("{$year}-12-31", new DateTimeZone(TIMEZONE_VIETNAM));

    return ['start' => $start, 'end' => $end];
}

/**
 * Get date range based on predefined ranges
 * @param string $range 'today', 'week', 'month', or 'year'
 * @return array ['from' => string, 'to' => string]
 */
/**
 * Get date range based on predefined ranges
 * @param string $range 'today', 'week', 'month', or 'year'
 * @return array ['from' => string, 'to' => string]
 */
function getDateRange($range)
{
    date_default_timezone_set(TIMEZONE_VIETNAM);

    $today = new DateTime('now', new DateTimeZone(TIMEZONE_VIETNAM));
    $today->setTime(0, 0, 0);

    $year = (int) $today->format('Y');
    $month = (int) $today->format('n'); // 1-12

    $from = "";
    $to = "";

    switch ($range) {
        case 'today':
            $dateStr = $today->format('Y-m-d');
            $from = $dateStr;
            $to = $dateStr;
            break;

        case 'week':
            $weekRange = calculateWeekRange($today);
            $from = $weekRange['start']->format('Y-m-d');
            $to = $weekRange['end']->format('Y-m-d');
            break;

        case 'month':
            $monthRange = calculateMonthRange($year, $month);
            $from = $monthRange['start']->format('Y-m-d');
            $to = $monthRange['end']->format('Y-m-d');
            break;

        case 'year':
            $yearRange = calculateYearRange($year);
            $from = $yearRange['start']->format('Y-m-d');
            $to = $yearRange['end']->format('Y-m-d');
            break;

        default:
            $from = "";
            $to = "";
            break;
    }

    return ['from' => $from, 'to' => $to];
}

function getTodayISO()
{
    date_default_timezone_set(TIMEZONE_VIETNAM);
    return date('Y-m-d');
}