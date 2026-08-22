<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'] ?: null;
    $unit_price = (float)$_POST['unit_price'];
    $cost_price = (float)$_POST['cost_price'];
    $quantity = (int)$_POST['quantity'];
    $min_quantity = (int)$_POST['min_quantity'];
    $expiry_date = $_POST['expiry_date'];
    $supplier_id = (int)$_POST['supplier_id'] ?: null;
    $barcode = trim($_POST['barcode']);
    $description = trim($_POST['description']);
    $is_favorite = isset($_POST['is_favorite']) ? 1 : 0;

    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('med_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $imageName);
    }

    if ($name === '' || $unit_price <= 0) {
        $error = "الرجاء تعبئة اسم المنتج والسعر بشكل صحيح";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, unit_price, cost_price, quantity, min_quantity, expiry_date, supplier_id, barcode, description, image, is_favorite, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1)");
        $stmt->execute([$name, $category_id, $unit_price, $cost_price, $quantity, $min_quantity, $expiry_date, $supplier_id, $barcode, $description, $imageName, $is_favorite]);
        header("Location: list.php");
        exit();
    }
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="mb-4">➕ إضافة منتج جديد</h2>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="form-glass" style="max-width:600px">
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">اسم المنتج</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">التصنيف</label>
            <select name="category_id" class="form-select">
                <option value="">-- بدون --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">سعر البيع</label>
            <input type="number" step="0.01" name="unit_price" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">سعر التكلفة</label>
            <input type="number" step="0.01" name="cost_price" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">الكمية</label>
            <input type="number" name="quantity" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">الحد الأدنى للتنبيه</label>
            <input type="number" name="min_quantity" class="form-control" value="10">
        </div>
        <div class="col-md-6">
            <label class="form-label">تاريخ الصلاحية</label>
            <input type="date" name="expiry_date" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">الباركود</label>
            <input type="text" name="barcode" class="form-control">
        </div>
        <div class="col-md-12">
            <label class="form-label">المورد</label>
            <select name="supplier_id" class="form-select">
                <option value="">-- بدون --</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-12">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">صورة المنتج</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="col-md-12 mt-3">
            <input type="checkbox" name="is_favorite" id="fav"> <label for="fav">إضافة للمفضلة (تظهر بسرعة بشاشة البيع)</label>
        </div>
    </div>
    <button type="submit" class="btn btn-orange mt-4">حفظ</button>
    <a href="list.php" class="btn btn-outline-orange mt-4">إلغاء</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
