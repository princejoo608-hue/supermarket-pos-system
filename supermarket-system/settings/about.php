<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">ℹ️ عن النظام</h2>

<div class="glass-card" style="max-width:600px">
    <div class="text-center mb-3">
        <div class="login-seal" style="margin:0 auto">🛒</div>
    </div>
    <h4 class="text-center">Ajimi POS — نظام إدارة السوبر ماركت</h4>
    <p class="text-center text-muted">الجودة التي تستحقها، والسرعة التي تحتاجها.</p>
    <hr>
    <table class="table-glass">
        <tbody>
            <tr><td>المطوّر</td><td><strong>Ajimi Systems</strong></td></tr>
            <tr><td>الإصدار</td><td>1.0.0</td></tr>
            <tr><td>التقنيات</td><td>PHP 8, MySQL, PDO, Bootstrap 5</td></tr>
        </tbody>
    </table>
    <p class="text-muted small mt-3">* بيانات المطوّر تظهر بهذه الصفحة فقط ولا تُطبع على الفواتير.</p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
