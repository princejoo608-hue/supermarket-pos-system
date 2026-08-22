<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['delete'])) {
    // Soft delete
    $stmt = $pdo->prepare("UPDATE products SET active = 0 WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: list.php");
    exit();
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT m.*, c.name AS category_name
        FROM products m
        LEFT JOIN categories c ON m.category_id = c.id
        WHERE m.active = 1";
$params = [];
if ($search !== '') {
    $sql .= " AND m.name LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY m.name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2>🛒 إدارة المنتجات</h2>
    <a href="add.php" class="btn btn-orange">+ إضافة منتج</a>
</div>

<form method="GET" class="mb-3">
    <input type="text" name="q" class="form-control pos-search" placeholder="ابحث عن منتج بالاسم..." value="<?php echo htmlspecialchars($search); ?>" onkeyup="if(event.key==='Enter')this.form.submit()">
</form>

<div class="table-responsive">
    <table class="table-glass">
        <thead>
            <tr>
                <th>الصورة</th><th>الاسم</th><th>التصنيف</th><th>السعر</th><th>سعر التكلفة</th>
                <th>الكمية</th><th>الصلاحية</th><th>مفضل</th><th>الحالة</th><th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $row):
                $expiry = strtotime($row['expiry_date']);
                $daysLeft = ($expiry - strtotime(date('Y-m-d'))) / 86400;
                $statusClass = "badge-ok"; $statusText = "جيد";
                if ($row['quantity'] <= $row['min_quantity']) { $statusClass = "badge-warning"; $statusText = "مخزون منخفض"; }
                if ($daysLeft <= 30) { $statusClass = "badge-danger"; $statusText = "قرب الانتهاء"; }
                if ($daysLeft < 0) { $statusClass = "badge-danger"; $statusText = "منتهي"; }
            ?>
            <tr>
                <td>
                    <?php if ($row['image']): ?>
                        <img src="/supermarket-system/assets/uploads/<?php echo htmlspecialchars($row['image']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                    <?php else: ?>
                        <span style="opacity:.3">🛒</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['category_name'] ?? '-'); ?></td>
                <td><?php echo number_format($row['unit_price'], 2); ?></td>
                <td><?php echo number_format($row['cost_price'], 2); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['expiry_date']; ?></td>
                <td><?php echo $row['is_favorite'] ? '⭐' : '—'; ?></td>
                <td><span class="badge-pill <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-orange btn-sm">تعديل</a>
                    <a href="list.php?delete=<?php echo $row['id']; ?>" class="btn btn-soft-danger btn-sm" onclick="return confirm('حذف هذا المنتج؟')">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <tr><td colspan="10" class="text-center text-muted py-3">لا توجد منتجات</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
