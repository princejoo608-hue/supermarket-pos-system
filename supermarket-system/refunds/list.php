<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$invoice_no = trim($_GET['invoice'] ?? '');
$sale = null; $items = [];

if ($invoice_no !== '') {
    $stmt = $pdo->prepare("SELECT s.*, u.full_name AS cashier_name FROM sales s JOIN users u ON s.user_id=u.id WHERE s.global_invoice_no = ?");
    $stmt->execute([$invoice_no]);
    $sale = $stmt->fetch();
    if ($sale) {
        $stmt = $pdo->prepare("
            SELECT si.*, m.name AS product_name,
                COALESCE((SELECT SUM(ri.quantity) FROM refund_items ri JOIN refunds r ON ri.refund_id=r.id WHERE r.sale_id=si.sale_id AND ri.product_id=si.product_id),0) AS already_refunded
            FROM sale_items si JOIN products m ON si.product_id=m.id WHERE si.sale_id=?");
        $stmt->execute([$sale['id']]);
        $items = $stmt->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_id = (int)$_POST['sale_id'];
    $refundItems = json_decode($_POST['refund_items'], true);

    try {
        $pdo->beginTransaction();
        $total = 0;
        foreach ($refundItems as $ri) $total += $ri['qty'] * $ri['price'];

        $stmt = $pdo->prepare("INSERT INTO refunds (sale_id, user_id, total_amount, refund_date) VALUES (?,?,?,NOW())");
        $stmt->execute([$sale_id, $_SESSION['user_id'], $total]);
        $refund_id = $pdo->lastInsertId();

        foreach ($refundItems as $ri) {
            $pdo->prepare("INSERT INTO refund_items (refund_id, product_id, quantity, price_at_refund) VALUES (?,?,?,?)")
                ->execute([$refund_id, $ri['product_id'], $ri['qty'], $ri['price']]);
            // إعادة الكمية للمخزون
            $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")->execute([$ri['qty'], $ri['product_id']]);
        }
        $pdo->commit();
        header("Location: list.php?done=1");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "حدث خطأ أثناء المرتجع";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">↩️ المرتجعات</h2>
<?php if (isset($_GET['done'])): ?><div class="alert alert-success">تم تنفيذ المرتجع بنجاح وتحديث المخزون تلقائياً.</div><?php endif; ?>

<form method="GET" class="mb-4 d-flex gap-2" style="max-width:400px">
    <input type="text" name="invoice" class="form-control" placeholder="رقم الفاتورة" value="<?php echo htmlspecialchars($invoice_no); ?>">
    <button class="btn btn-orange">بحث</button>
</form>

<?php if ($invoice_no !== '' && !$sale): ?>
    <div class="alert alert-warning">لا توجد فاتورة بهذا الرقم</div>
<?php endif; ?>

<?php if ($sale): ?>
<div class="glass-card">
    <h5>فاتورة #<?php echo $sale['global_invoice_no']; ?> — الكاشير: <?php echo htmlspecialchars($sale['cashier_name']); ?></h5>
    <form method="POST" id="refundForm">
        <input type="hidden" name="sale_id" value="<?php echo $sale['id']; ?>">
        <input type="hidden" name="refund_items" id="refundItemsInput">
        <table class="table-glass mt-3">
            <thead><tr><th>المنتج</th><th>الكمية بالفاتورة</th><th>مرتجع سابقاً</th><th>كمية الإرجاع</th><th>السعر</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it):
                $available = $it['quantity'] - $it['already_refunded']; ?>
                <tr>
                    <td><?php echo htmlspecialchars($it['product_name']); ?></td>
                    <td><?php echo $it['quantity']; ?></td>
                    <td><?php echo $it['already_refunded']; ?></td>
                    <td><input type="number" class="form-control form-control-sm refund-qty" style="width:90px" min="0" max="<?php echo $available; ?>" value="0"
                        data-id="<?php echo $it['product_id']; ?>" data-price="<?php echo $it['price_at_sale']; ?>"></td>
                    <td><?php echo number_format($it['price_at_sale'],2); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-orange mt-2">تنفيذ المرتجع</button>
    </form>
</div>
<script>
document.getElementById('refundForm').addEventListener('submit', function(e) {
    const items = [];
    document.querySelectorAll('.refund-qty').forEach(inp => {
        const qty = parseInt(inp.value) || 0;
        if (qty > 0) items.push({ product_id: inp.dataset.id, qty: qty, price: parseFloat(inp.dataset.price) });
    });
    if (items.length === 0) { alert('حدد كمية إرجاع لمنتج واحد على الأقل'); e.preventDefault(); return; }
    document.getElementById('refundItemsInput').value = JSON.stringify(items);
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
