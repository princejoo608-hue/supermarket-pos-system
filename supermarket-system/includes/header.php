<?php
require_once __DIR__ . '/functions.php';
$storeName = getSetting($pdo, 'store_name', 'سوبر ماركت البركة');
$logo = getSetting($pdo, 'logo', '');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($storeName); ?> | Ajimi POS</title>
    <link rel="stylesheet" href="/supermarket-system/assets/css/style.css">
</head>
<body>
<nav class="app-navbar">
    <div class="container-fluid px-4 d-flex align-items-center justify-content-between">
        <a class="navbar-brand" href="/supermarket-system/index.php">
            <span class="brand-seal">🛒</span>
            <span><?php echo htmlspecialchars($storeName); ?></span>
        </a>
        <button class="navbar-toggler" type="button">☰</button>
        <div class="navbar-collapse" id="navMenu">
            <ul class="navbar-nav gap-1">
                <li><a class="nav-link" href="/supermarket-system/index.php">الرئيسية</a></li>
                <li><a class="nav-link" href="/supermarket-system/sales/pos.php">شاشة البيع</a></li>
                <li><a class="nav-link" href="/supermarket-system/products/list.php">المنتجات</a></li>
                <li><a class="nav-link" href="/supermarket-system/categories/list.php">التصنيفات</a></li>
                <li><a class="nav-link" href="/supermarket-system/customers/list.php">العملاء</a></li>
                <li><a class="nav-link" href="/supermarket-system/suppliers/list.php">الموردين</a></li>
                <li><a class="nav-link" href="/supermarket-system/refunds/list.php">المرتجعات</a></li>
                <li><a class="nav-link" href="/supermarket-system/reports/dashboard.php">التقارير</a></li>
                <?php if (isAdmin()): ?>
                <li class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#">الإدارة ▾</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="/supermarket-system/users/list.php">المستخدمين</a>
                        <a class="dropdown-item" href="/supermarket-system/payment_methods/list.php">طرق الدفع</a>
                        <a class="dropdown-item" href="/supermarket-system/settings/index.php">الإعدادات</a>
                        <a class="dropdown-item" href="/supermarket-system/settings/about.php">عن النظام</a>
                    </div>
                </li>
                <?php endif; ?>
                <li><span class="nav-link user-pill">👤 <?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></span></li>
                <li><a class="nav-link logout-link" href="/supermarket-system/auth/logout.php">خروج</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid px-4 py-4">
