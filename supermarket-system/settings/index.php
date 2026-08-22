<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['store_name','phone','address','currency','paper_size','footer_message','print_logo','print_phone','print_address'];
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? '';
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$f, $val, $val]);
    }
    if (!empty($_FILES['logo']['name'])) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logoName = 'logo.' . $ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../assets/uploads/' . $logoName);
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$logoName, $logoName]);
    }
    header("Location: index.php?saved=1");
    exit();
}

$s = getSettings($pdo);
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">⚙️ الإعدادات</h2>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">تم حفظ الإعدادات بنجاح</div><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="form-glass" style="max-width:650px">
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">اسم السوبر ماركت</label>
            <input type="text" name="store_name" class="form-control" value="<?php echo htmlspecialchars($s['store_name'] ?? 'سوبر ماركت البركة'); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">الهاتف</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($s['phone'] ?? ''); ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">العنوان</label>
            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($s['address'] ?? ''); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">العملة</label>
            <input type="text" name="currency" class="form-control" value="<?php echo htmlspecialchars($s['currency'] ?? 'ج.س'); ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">حجم الفاتورة الحرارية</label>
            <select name="paper_size" class="form-select">
                <option value="80mm" <?php echo ($s['paper_size']??'80mm')==='80mm'?'selected':''; ?>>80mm</option>
                <option value="58mm" <?php echo ($s['paper_size']??'')==='58mm'?'selected':''; ?>>58mm</option>
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">رسالة أسفل الفاتورة</label>
            <input type="text" name="footer_message" class="form-control" value="<?php echo htmlspecialchars($s['footer_message'] ?? 'شكراً لزيارتكم'); ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">شعار السوبر ماركت</label>
            <input type="file" name="logo" class="form-control" accept="image/*">
        </div>
        <div class="col-md-4 mt-3"><input type="checkbox" name="print_logo" value="1" <?php echo ($s['print_logo']??'1')?'checked':''; ?>> طباعة الشعار</div>
        <div class="col-md-4 mt-3"><input type="checkbox" name="print_phone" value="1" <?php echo ($s['print_phone']??'1')?'checked':''; ?>> طباعة الهاتف</div>
        <div class="col-md-4 mt-3"><input type="checkbox" name="print_address" value="1" <?php echo ($s['print_address']??'1')?'checked':''; ?>> طباعة العنوان</div>
    </div>
    <button type="submit" class="btn btn-orange mt-4">حفظ الإعدادات</button>
</form>

<div class="mt-4">
    <a href="/supermarket-system/database/supermarket_db.sql" class="btn btn-outline-orange btn-sm" download>⬇️ نسخة احتياطية (ملف SQL الأصلي)</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
