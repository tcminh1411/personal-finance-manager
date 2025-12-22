<?php
// Hàm format tiền tệ
function formatMoney($amount)
{
    return number_format($amount, 0, ',', '.') . ' Đ';
}

// Hàm escape chuỗi HTML (Chống XSS)
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

