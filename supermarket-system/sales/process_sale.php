<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['items']) || empty($data['payments'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات ناقصة']);
    exit();
}

$items = $data['items'];
$customer_id = (int)$data['customer_id'];
$discount = (float)$data['discount'];
$payments = $data['payments'];
$user_id = $_SESSION['user_id'];

$subtotal = 0;
foreach ($items as $i) $subtotal += $i['price'] * $i['qty'];
$total = max($subtotal - $discount, 0);

try {
    $pdo->beginTransaction();

    [$globalNo, $dailyNo] = generateInvoiceNumbers($pdo);

    $stmt = $pdo->prepare("INSERT INTO sales (global_invoice_no, daily_invoice_no, user_id, customer_id, subtotal, discount, total_amount, sale_date) VALUES (?,?,?,?,?,?,?,NOW())");
    $stmt->execute([$globalNo, $dailyNo, $user_id, $customer_id, $subtotal, $discount, $total]);
    $sale_id = $pdo->lastInsertId();

    foreach ($items as $item) {
        $product_id = (int)$item['id'];
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];

        $check = $pdo->prepare("SELECT quantity FROM products WHERE id = ? FOR UPDATE");
        $check->execute([$product_id]);
        $stock = $check->fetch();
        if (!$stock || $stock['quantity'] < $qty) throw new Exception('الكمية غير كافية لأحد المنتجات');

        $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES (?,?,?,?)")
            ->execute([$sale_id, $product_id, $qty, $price]);

        $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")->execute([$qty, $product_id]);
    }

    foreach ($payments as $p) {
        $pdo->prepare("INSERT INTO sale_payments (sale_id, payment_method_id, amount) VALUES (?,?,?)")
            ->execute([$sale_id, (int)$p['method_id'], (float)$p['amount']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'sale_id' => $sale_id]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
