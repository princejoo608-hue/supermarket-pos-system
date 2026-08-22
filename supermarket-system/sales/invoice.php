<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$sale_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT s.*, u.full_name AS cashier_name, c.name AS customer_name
    FROM sales s JOIN users u ON s.user_id = u.id LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.id = ?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch();
if (!$sale) die("الفاتورة غير موجودة");

$stmt = $pdo->prepare("SELECT si.*, m.name AS product_name FROM sale_items si JOIN products m ON si.product_id = m.id WHERE si.sale_id = ?");
$stmt->execute([$sale_id]);
$items = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT sp.*, pm.name AS method_name FROM sale_payments sp JOIN payment_methods pm ON sp.payment_method_id = pm.id WHERE sp.sale_id = ?");
$stmt->execute([$sale_id]);
$payments = $stmt->fetchAll();

$paperSize = getSetting($pdo, 'paper_size', '80mm');
$storeName = getSetting($pdo, 'store_name', 'سوبر ماركت البركة');
$phone = getSetting($pdo, 'phone', '');
$address = getSetting($pdo, 'address', '');
$footerMsg = getSetting($pdo, 'footer_message', 'شكراً لزيارتكم');
$paidTotal = array_sum(array_column($payments, 'amount'));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="receipt-box receipt-<?php echo $paperSize; ?>" id="printArea">
    <div class="text-center">
        <?php if (getSetting($pdo,'print_logo','1')): ?><div style="font-size:26px">🛒</div><?php endif; ?>
        <h5 class="mb-0"><?php echo htmlspecialchars($storeName); ?></h5>
        <?php if ($phone): ?><small><?php echo htmlspecialchars($phone); ?></small><br><?php endif; ?>
        <?php if ($address): ?><small><?php echo htmlspecialchars($address); ?></small><?php endif; ?>
    </div>
    <hr>
    <p class="mb-1">فاتورة #<?php echo $sale['global_invoice_no']; ?> (يومي #<?php echo $sale['daily_invoice_no']; ?>)</p>
    <p class="mb-1 small">التاريخ: <?php echo $sale['sale_date']; ?></p>
    <p class="mb-1 small">الكاشير: <?php echo htmlspecialchars($sale['cashier_name']); ?></p>
    <p class="mb-1 small">العميل: <?php echo htmlspecialchars($sale['customer_name'] ?? 'عميل بيع عام'); ?></p>
    <hr>
    <table class="w-100 small">
        <?php foreach ($items as $it): ?>
        <tr>
            <td><?php echo htmlspecialchars($it['product_name']); ?></td>
            <td class="text-center"><?php echo $it['quantity']; ?></td>
            <td class="text-end"><?php echo number_format($it['quantity']*$it['price_at_sale'],2); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <hr>
    <p class="d-flex justify-content-between">المجموع الفرعي <span><?php echo number_format($sale['subtotal'],2); ?></span></p>
    <p class="d-flex justify-content-between">الخصم <span><?php echo number_format($sale['discount'],2); ?></span></p>
    <p class="d-flex justify-content-between fw-bold">الإجمالي <span><?php echo money($sale['total_amount'], $pdo); ?></span></p>
    <hr>
    <?php foreach ($payments as $p): ?>
        <p class="d-flex justify-content-between small"><?php echo htmlspecialchars($p['method_name']); ?> <span><?php echo number_format($p['amount'],2); ?></span></p>
    <?php endforeach; ?>
    <p class="d-flex justify-content-between small">المدفوع <span><?php echo number_format($paidTotal,2); ?></span></p>
    <p class="d-flex justify-content-between small">الباقي <span><?php echo number_format($paidTotal - $sale['total_amount'],2); ?></span></p>
    <hr>
    <p class="text-center small"><?php echo htmlspecialchars($footerMsg); ?></p>
</div>

<div class="no-print mt-3">
    <button onclick="window.print()" class="btn btn-orange">🖨️ طباعة</button>
    <a href="pos.php" class="btn btn-outline-orange">فاتورة جديدة</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
