<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username === '' || $full_name === '' || $password === '') {
        $error = "جميع الحقول مطلوبة";
    } else {
        // كلمة المرور تُخزّن مشفّرة (bcrypt) وليس نص عادي، حماية أساسية لبيانات النظام
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$username, $hash, $full_name, $role]);
            header("Location: list.php");
            exit();
        } catch (PDOException $e) {
            $error = "اسم المستخدم موجود مسبقاً";
        }
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("UPDATE users SET active = 0 WHERE id = ? AND id != ?");
    $stmt->execute([(int)$_GET['delete'], $_SESSION['user_id']]);
    header("Location: list.php");
    exit();
}

$users = $pdo->query("SELECT * FROM users WHERE active = 1 ORDER BY full_name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">👤 المستخدمين</h2>

<div class="row g-4">
    <div class="col-md-4">
        <div class="form-glass">
            <h5>إضافة مستخدم</h5>
            <?php if ($error): ?><div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="full_name" class="form-control" required>
                <label class="form-label mt-2">اسم المستخدم</label>
                <input type="text" name="username" class="form-control" required>
                <label class="form-label mt-2">كلمة المرور</label>
                <input type="password" name="password" class="form-control" required>
                <label class="form-label mt-2">الصلاحية</label>
                <select name="role" class="form-select">
                    <option value="cashier">كاشير</option>
                    <option value="admin">مدير</option>
                </select>
                <button type="submit" class="btn btn-orange w-100 mt-3">إضافة</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="table-responsive">
            <table class="table-glass">
                <thead><tr><th>الاسم</th><th>اسم المستخدم</th><th>الصلاحية</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><span class="badge-pill <?php echo $u['role']==='admin'?'badge-ok':'badge-warning'; ?>"><?php echo $u['role']==='admin'?'مدير':'كاشير'; ?></span></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="list.php?delete=<?php echo $u['id']; ?>" class="btn btn-soft-danger btn-sm" onclick="return confirm('حذف هذا المستخدم؟')">حذف</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
