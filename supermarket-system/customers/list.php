<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    if ($name === '') {
        $error = "اسم العميل مطلوب";
    } else {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
        $stmt->execute([$name, $phone]);
        header("Location: list.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // منع حذف العميل الافتراضي
    $check = $pdo->prepare("SELECT is_default FROM customers WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()['is_default']) {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: list.php");
    exit();
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY is_default DESC, name ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">👥 العملاء</h2>

<div class="row g-4">
    <div class="col-md-4">
        <div class="form-glass">
            <h5>إضافة عميل</h5>
            <?php if ($error): ?><div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <label class="form-label">الاسم</label>
                <input type="text" name="name" class="form-control" required>
                <label class="form-label mt-2">الهاتف</label>
                <input type="text" name="phone" class="form-control">
                <button type="submit" class="btn btn-orange w-100 mt-3">إضافة</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table-glass">
                <thead><tr><th>الاسم</th><th>الهاتف</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['name']); ?> <?php echo $c['is_default'] ? '<span class="badge-pill badge-ok">افتراضي</span>' : ''; ?></td>
                            <td><?php echo htmlspecialchars($c['phone']); ?></td>
                            <td><?php if (!$c['is_default']): ?><a href="list.php?delete=<?php echo $c['id']; ?>" class="btn btn-soft-danger btn-sm" onclick="return confirm('حذف؟')">حذف</a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
