<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) AS total, COUNT(*) AS cnt FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch();
$refundCount = $pdo->query("SELECT COUNT(*) AS cnt FROM refunds WHERE DATE(refund_date) = CURDATE()")->fetch();
$lowStock = $pdo->query("SELECT COUNT(*) AS cnt FROM products WHERE quantity <= min_quantity")->fetch();

$recentSales = $pdo->query("
    SELECT s.id, s.global_invoice_no, s.total_amount, s.sale_date, u.full_name AS cashier, c.name AS customer_name
    FROM sales s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN customers c ON s.customer_id = c.id
    ORDER BY s.id DESC LIMIT 8
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if (isset($_GET['denied'])): ?>
    <div class="alert alert-warning">هذه الصفحة متاحة للمدير فقط.</div>
<?php endif; ?>

<h2 class="mb-1">أهلاً، <?php echo htmlspecialchars($_SESSION['full_name']); ?> 👋</h2>
<p class="text-muted mb-4">جلستك الحالية: <strong><?php echo $_SESSION['role'] === 'admin' ? 'مدير النظام' : 'كاشير'; ?></strong></p>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card success">
            <div class="stat-label">مبيعات اليوم</div>
            <div class="stat-value"><?php echo money($todaySales['total'], $pdo); ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-label">عدد فواتير اليوم</div>
            <div class="stat-value"><?php echo (int)$todaySales['cnt']; ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card <?php echo $refundCount['cnt'] > 0 ? 'alert' : ''; ?>">
            <div class="stat-label">مرتجعات اليوم</div>
            <div class="stat-value"><?php echo (int)$refundCount['cnt']; ?></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card <?php echo $lowStock['cnt'] > 0 ? 'alert' : ''; ?>">
            <div class="stat-label">تنبيهات مخزون منخفض</div>
            <div class="stat-value"><?php echo (int)$lowStock['cnt']; ?></div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap mb-4">
    <a href="sales/pos.php" class="btn btn-orange">🛒 بدء عملية بيع</a>
    <a href="products/list.php" class="btn btn-outline-orange">🛒 إدارة المنتجات</a>
    <a href="reports/dashboard.php" class="btn btn-outline-orange">📊 التقارير</a>
</div>

<div class="glass-card">
    <h5 class="mb-3">آخر المبيعات</h5>
    <div class="table-responsive">
        <table class="table-glass">
            <thead>
                <tr><th>#الفاتورة</th><th>العميل</th><th>الكاشير</th><th>الإجمالي</th><th>الوقت</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentSales as $s): ?>
                    <tr>
                        <td>#<?php echo $s['global_invoice_no']; ?></td>
                        <td><?php echo htmlspecialchars($s['customer_name'] ?? 'عميل بيع عام'); ?></td>
                        <td><?php echo htmlspecialchars($s['cashier']); ?></td>
                        <td><?php echo money($s['total_amount'], $pdo); ?></td>
                        <td><?php echo date('H:i', strtotime($s['sale_date'])); ?></td>
                        <td><a href="sales/invoice.php?id=<?php echo $s['id']; ?>" class="btn btn-outline-orange btn-sm">عرض</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentSales)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">لا توجد مبيعات بعد</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
