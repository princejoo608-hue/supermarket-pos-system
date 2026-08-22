<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    if ($name === '') {
        $error = "اسم المورد مطلوب";
    } else {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $address]);
        header("Location: list.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: list.php");
    exit();
}

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">🚚 إدارة الموردين</h2>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="form-glass">
            <h5>إضافة مورد جديد</h5>
            <form method="POST">
                <label class="form-label">اسم المورد</label>
                <input type="text" name="name" class="form-control" required>
                <label class="form-label mt-2">الهاتف</label>
                <input type="text" name="phone" class="form-control">
                <label class="form-label mt-2">العنوان</label>
                <input type="text" name="address" class="form-control">
                <button type="submit" class="btn btn-orange w-100 mt-3">إضافة</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table-glass">
                <thead><tr><th>الاسم</th><th>الهاتف</th><th>العنوان</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo htmlspecialchars($s['phone']); ?></td>
                            <td><?php echo htmlspecialchars($s['address']); ?></td>
                            <td><a href="list.php?delete=<?php echo $s['id']; ?>" class="btn btn-soft-danger btn-sm" onclick="return confirm('حذف؟')">حذف</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
