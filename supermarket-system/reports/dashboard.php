<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$cashierId = $_GET['cashier_id'] ?? '';
$customerId = $_GET['customer_id'] ?? '';
$categoryId = $_GET['category_id'] ?? '';
$paymentId = $_GET['payment_id'] ?? '';

$where = "WHERE DATE(s.sale_date) BETWEEN ? AND ?";
$params = [$dateFrom, $dateTo];
if ($cashierId) { $where .= " AND s.user_id = ?"; $params[] = $cashierId; }
if ($customerId) { $where .= " AND s.customer_id = ?"; $params[] = $customerId; }
if ($paymentId) { $where .= " AND EXISTS(SELECT 1 FROM sale_payments sp WHERE sp.sale_id=s.id AND sp.payment_method_id=?)"; $params[] = $paymentId; }

$sql = "SELECT s.*, u.full_name AS cashier, c.name AS customer_name FROM sales s
        JOIN users u ON s.user_id=u.id LEFT JOIN customers c ON s.customer_id=c.id
        $where ORDER BY s.sale_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

$totalSum = array_sum(array_column($sales, 'total_amount'));

$cashiers = $pdo->query("SELECT id, full_name FROM users ORDER BY full_name")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$paymentMethods = $pdo->query("SELECT id, name FROM payment_methods ORDER BY id")->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sales_report.csv"');
    echo "\xEF\xBB\xBF"; // BOM لدعم العربي بإكسل
    $out = fopen('php://output', 'w');
    fputcsv($out, ['رقم الفاتورة', 'التاريخ', 'العميل', 'الكاشير', 'الإجمالي']);
    foreach ($sales as $s) {
        fputcsv($out, [$s['global_invoice_no'], $s['sale_date'], $s['customer_name'] ?? 'عميل بيع عام', $s['cashier'], $s['total_amount']]);
    }
    fclose($out);
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">📊 التقارير</h2>

<form method="GET" class="glass-card mb-4">
    <div class="row g-2">
        <div class="col-md-2"><label class="small">من</label><input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $dateFrom; ?>"></div>
        <div class="col-md-2"><label class="small">إلى</label><input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $dateTo; ?>"></div>
        <div class="col-md-2">
            <label class="small">الكاشير</label>
            <select name="cashier_id" class="form-select form-select-sm">
                <option value="">الكل</option>
                <?php foreach ($cashiers as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $cashierId==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['full_name']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small">العميل</label>
            <select name="customer_id" class="form-select form-select-sm">
                <option value="">الكل</option>
                <?php foreach ($customers as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $customerId==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="small">طريقة الدفع</label>
            <select name="payment_id" class="form-select form-select-sm">
                <option value="">الكل</option>
                <?php foreach ($paymentMethods as $p): ?><option value="<?php echo $p['id']; ?>" <?php echo $paymentId==$p['id']?'selected':''; ?>><?php echo htmlspecialchars($p['name']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end"><button class="btn btn-orange w-100 btn-sm">تصفية</button></div>
    </div>
</form>

<div class="stat-card success mb-4" style="max-width:280px">
    <div class="stat-label">إجمالي المبيعات بالفترة</div>
    <div class="stat-value"><?php echo money($totalSum, $pdo); ?></div>
</div>

<div class="d-flex gap-2 mb-3">
    <button onclick="window.print()" class="btn btn-outline-orange btn-sm">🖨️ طباعة</button>
    <a href="?<?php echo http_build_query(array_merge($_GET, ['export'=>'csv'])); ?>" class="btn btn-outline-orange btn-sm">⬇️ تصدير Excel (CSV)</a>
</div>

<div class="table-responsive">
    <table class="table-glass">
        <thead><tr><th>#الفاتورة</th><th>التاريخ</th><th>العميل</th><th>الكاشير</th><th>الإجمالي</th></tr></thead>
        <tbody>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td>#<?php echo $s['global_invoice_no']; ?></td>
                    <td><?php echo $s['sale_date']; ?></td>
                    <td><?php echo htmlspecialchars($s['customer_name'] ?? 'عميل بيع عام'); ?></td>
                    <td><?php echo htmlspecialchars($s['cashier']); ?></td>
                    <td><?php echo number_format($s['total_amount'],2); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($sales)): ?><tr><td colspan="5" class="text-center text-muted py-3">لا توجد نتائج</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
