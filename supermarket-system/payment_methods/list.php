<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name === '') {
        $error = "الاسم مطلوب";
    } else {
        $stmt = $pdo->prepare("INSERT INTO payment_methods (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: list.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: list.php");
    exit();
}

$methods = $pdo->query("SELECT * FROM payment_methods ORDER BY id")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">💳 طرق الدفع</h2>
<p class="text-muted">أضف أو احذف طرق الدفع اللي بتستخدمها السوبر ماركت (بدون حد أقصى).</p>

<div class="row g-4">
    <div class="col-md-4">
        <div class="form-glass">
            <h5>إضافة طريقة دفع</h5>
            <?php if ($error): ?><div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <label class="form-label">الاسم</label>
                <input type="text" name="name" class="form-control" placeholder="مثال: بنكك، فوري..." required>
                <button type="submit" class="btn btn-orange w-100 mt-3">إضافة</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table-glass">
                <thead><tr><th>الاسم</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($methods as $m): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m['name']); ?></td>
                            <td><a href="list.php?delete=<?php echo $m['id']; ?>" class="btn btn-soft-danger btn-sm" onclick="return confirm('حذف؟')">حذف</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
