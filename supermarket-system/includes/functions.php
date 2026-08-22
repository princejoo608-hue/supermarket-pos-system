<?php
// دوال مساعدة تستخدم بكل النظام

// جلب إعدادات النظام (اسم السوبر ماركت، الشعار، العملة... إلخ) من جدول settings
function getSettings($pdo) {
    static $settings = null;
    if ($settings === null) {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function getSetting($pdo, $key, $default = '') {
    $settings = getSettings($pdo);
    return $settings[$key] ?? $default;
}

// توليد رقم فاتورة عام + يومي (اليومي يبدأ من 1 كل يوم)
function generateInvoiceNumbers($pdo) {
    $today = date('Y-m-d');

    // الرقم العام (يزيد باستمرار)
    $global = (int)$pdo->query("SELECT COALESCE(MAX(global_invoice_no),0) AS m FROM sales")->fetch()['m'] + 1;

    // الرقم اليومي (يبدأ من 1 كل يوم)
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(daily_invoice_no),0) AS m FROM sales WHERE DATE(sale_date) = ?");
    $stmt->execute([$today]);
    $daily = (int)$stmt->fetch()['m'] + 1;

    return [$global, $daily];
}

// تنسيق المبلغ المالي
function money($amount, $pdo = null) {
    $currency = $pdo ? getSetting($pdo, 'currency', 'ج.س') : 'ج.س';
    return number_format((float)$amount, 2) . ' ' . $currency;
}

// التحقق من صلاحية المدير
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// منع الكاشير من دخول صفحات المدير فقط
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: /supermarket-system/index.php?denied=1");
        exit();
    }
}
