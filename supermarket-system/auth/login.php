<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = "";

// جلب المستخدمين لعرضهم بقائمة منسدلة (بدل كتابة اسم المستخدم يدوياً)
$users = $pdo->query("SELECT id, username, full_name, role FROM users WHERE active = 1 ORDER BY full_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ? AND active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        header("Location: /supermarket-system/index.php");
        exit();
    } else {
        $error = "بيانات الدخول غير صحيحة";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | Ajimi POS</title>
    <link rel="stylesheet" href="/supermarket-system/assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-seal">🛒</div>
        <h2>سوبر ماركت البركة</h2>
        <p class="login-sub">الجودة التي تستحقها، والسرعة التي تحتاجها</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="text-start">
            <label class="form-label">المستخدم</label>
            <select name="username" class="form-control form-select" required>
                <option value="">-- اختر المستخدم --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo htmlspecialchars($u['username']); ?>">
                        <?php echo htmlspecialchars($u['full_name']); ?> (<?php echo $u['role'] === 'admin' ? 'مدير' : 'كاشير'; ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="form-label mt-3">كلمة المرور</label>
            <input type="password" name="password" class="form-control" required>

            <button type="submit" class="btn btn-orange w-100 mt-4">دخول</button>
        </form>
        <p class="login-hint">تجريبي: admin / admin123</p>
    </div>
</body>
</html>
