<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name === '') {
        $error = "اسم التصنيف مطلوب";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: list.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: list.php");
    exit();
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE name LIKE ? ORDER BY name");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
}
$categories = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">🏷️ تصنيفات المنتجات</h2>

<div class="row g-4">
    <div class="col-md-4">
        <div class="form-glass">
            <h5>إضافة تصنيف جديد</h5>
            <?php if ($error): ?><div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <label class="form-label">اسم التصنيف</label>
                <input type="text" name="name" class="form-control" required>
                <button type="submit" class="btn btn-orange w-100 mt-3">إضافة</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <form method="GET" class="mb-3">
            <input type="text" name="q" class="form-control pos-search" placeholder="بحث عن تصنيف..." value="<?php echo htmlspecialchars($search); ?>" onchange="this.form.submit()">
        </form>
        <div class="table-responsive">
            <table class="table-glass">
                <thead><tr><th>الاسم</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><a href="list.php?delete=<?php echo $c['id']; ?>" class="btn btn-soft-danger btn-sm" onclick="return confirm('حذف هذا التصنيف؟')">حذف</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="2" class="text-center text-muted py-3">لا توجد تصنيفات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
