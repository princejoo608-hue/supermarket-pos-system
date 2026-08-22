<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$products = $pdo->query("
    SELECT m.id, m.name, m.unit_price, m.quantity, m.is_favorite, m.category_id, m.image
    FROM products m WHERE m.active = 1 AND m.quantity > 0 ORDER BY m.name
")->fetchAll();

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY is_default DESC, name")->fetchAll();
$paymentMethods = $pdo->query("SELECT id, name FROM payment_methods ORDER BY id")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <h2 class="mb-3">🛒 شاشة البيع</h2>
        <input type="text" id="searchBox" class="pos-search w-100 mb-3" placeholder="ابحث عن منتج بالاسم...">

        <div class="mb-3">
            <span class="category-pill active" data-cat="all" onclick="filterCategory(this,'all')">الكل</span>
            <span class="category-pill" data-cat="fav" onclick="filterCategory(this,'fav')">⭐ المفضلة</span>
            <?php foreach ($categories as $c): ?>
                <span class="category-pill" data-cat="<?php echo $c['id']; ?>" onclick="filterCategory(this,'<?php echo $c['id']; ?>')"><?php echo htmlspecialchars($c['name']); ?></span>
            <?php endforeach; ?>
        </div>

        <div class="row g-2" id="productGrid">
            <?php foreach ($products as $m): ?>
                <div class="col-6 col-md-4 col-lg-3 product-item"
                     data-cat="<?php echo $m['category_id']; ?>"
                     data-fav="<?php echo $m['is_favorite']; ?>"
                     data-name="<?php echo htmlspecialchars(strtolower($m['name'])); ?>">
                    <div class="product-card"
                         data-id="<?php echo $m['id']; ?>"
                         data-name="<?php echo htmlspecialchars($m['name']); ?>"
                         data-price="<?php echo $m['unit_price']; ?>"
                         data-stock="<?php echo $m['quantity']; ?>"
                         onclick="addToOrder(this)">
                        <?php if ($m['is_favorite']): ?><span class="fav-star">⭐</span><?php endif; ?>
                        <div class="p-name"><?php echo htmlspecialchars($m['name']); ?></div>
                        <div class="p-price"><?php echo number_format($m['unit_price'], 2); ?></div>
                        <div class="p-stock">متوفر: <?php echo $m['quantity']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="order-panel">
            <h5>الفاتورة الحالية</h5>

            <label class="form-label mt-2">العميل</label>
            <select id="customerId" class="form-select form-select-sm">
                <?php foreach ($customers as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <div id="orderItems" class="my-3"></div>
            <p class="text-center text-muted small" id="emptyMsg">لم تتم إضافة أي منتج بعد</p>

            <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="small">الخصم</span>
                <input type="number" id="discount" class="form-control form-control-sm" style="width:110px" value="0" min="0" onchange="renderOrder()">
            </div>

            <div class="grand-total-box">الإجمالي: <span id="grandTotal">0.00</span></div>

            <h6 class="mt-3">طرق الدفع</h6>
            <div id="paymentRows"></div>
            <button type="button" class="btn btn-outline-orange btn-sm w-100 mt-1" onclick="addPaymentRow()">+ إضافة طريقة دفع</button>

            <div class="d-flex justify-content-between small mt-3 pt-2" style="border-top:1px dashed #eee">
                <span>المدفوع: <strong id="paidAmount">0.00</strong></span>
                <span>المتبقي/الباقي: <strong id="remainingAmount">0.00</strong></span>
            </div>

            <button class="btn btn-orange w-100 mt-3" onclick="checkout()">✅ إتمام البيع</button>
            <button class="btn btn-soft-danger w-100 mt-2" onclick="clearOrder()">🗑️ إفراغ الفاتورة</button>
        </div>
    </div>
</div>

<script>
const paymentMethods = <?php echo json_encode($paymentMethods); ?>;
let order = [];
let paymentRows = [];

function filterCategory(el, cat) {
    document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.product-item').forEach(item => {
        let show = (cat === 'all') || (cat === 'fav' && item.dataset.fav === '1') || (item.dataset.cat === cat);
        item.style.display = show ? 'block' : 'none';
    });
}

document.getElementById('searchBox').addEventListener('keyup', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        item.style.display = item.dataset.name.includes(q) ? 'block' : 'none';
    });
});

function addToOrder(el) {
    const id = el.dataset.id, name = el.dataset.name, price = parseFloat(el.dataset.price), stock = parseInt(el.dataset.stock);
    const existing = order.find(i => i.id === id);
    if (existing) {
        if (existing.qty < stock) existing.qty++; else alert('الكمية غير كافية');
    } else {
        order.push({ id, name, price, qty: 1, stock });
    }
    renderOrder();
}

function changeQty(index, delta) {
    order[index].qty += delta;
    if (order[index].qty > order[index].stock) { order[index].qty = order[index].stock; alert('الكمية غير كافية'); }
    if (order[index].qty < 1) order.splice(index, 1);
    renderOrder();
}

function removeItem(index) { order.splice(index, 1); renderOrder(); }

function subtotal() { return order.reduce((s, i) => s + i.price * i.qty, 0); }
function grandTotal() {
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    return Math.max(subtotal() - discount, 0);
}

function renderOrder() {
    const box = document.getElementById('orderItems');
    document.getElementById('emptyMsg').style.display = order.length ? 'none' : 'block';
    box.innerHTML = order.map((item, i) => `
        <div class="order-row">
            <div style="flex:1">
                <div>${item.name}</div>
                <small class="text-muted">${item.price.toFixed(2)} × ${item.qty} = ${(item.price*item.qty).toFixed(2)}</small>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button class="qty-btn" onclick="changeQty(${i},-1)">−</button>
                <span>${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${i},1)">+</button>
                <button class="qty-btn" onclick="removeItem(${i})">✕</button>
            </div>
        </div>
    `).join('');
    document.getElementById('grandTotal').textContent = grandTotal().toFixed(2);
    renderPaymentSummary();
}

function addPaymentRow() {
    paymentRows.push({ method_id: paymentMethods[0]?.id || '', amount: 0, reference: '' });
    renderPaymentRows();
}
function removePaymentRow(i) { paymentRows.splice(i,1); renderPaymentRows(); }
function updatePaymentRow(i, field, val) { paymentRows[i][field] = val; renderPaymentSummary(); }

function renderPaymentRows() {
    const el = document.getElementById('paymentRows');
    el.innerHTML = paymentRows.map((p, i) => `
        <div class="payment-row">
            <select class="form-select form-select-sm" onchange="updatePaymentRow(${i},'method_id',this.value)">
                ${paymentMethods.map(m => `<option value="${m.id}" ${m.id==p.method_id?'selected':''}>${m.name}</option>`).join('')}
            </select>
            <input type="number" class="form-control form-control-sm" style="width:100px" value="${p.amount}" placeholder="المبلغ" onchange="updatePaymentRow(${i},'amount',parseFloat(this.value)||0)">
            <button class="qty-btn" onclick="removePaymentRow(${i})">✕</button>
        </div>
    `).join('');
    renderPaymentSummary();
}

function renderPaymentSummary() {
    const paid = paymentRows.reduce((s,p) => s + (parseFloat(p.amount)||0), 0);
    const total = grandTotal();
    document.getElementById('paidAmount').textContent = paid.toFixed(2);
    document.getElementById('remainingAmount').textContent = (paid - total).toFixed(2);
}

function clearOrder() { order = []; paymentRows = []; document.getElementById('discount').value = 0; renderOrder(); renderPaymentRows(); }

function checkout() {
    if (order.length === 0) { alert('الفاتورة فارغة'); return; }
    const paid = paymentRows.reduce((s,p) => s + (parseFloat(p.amount)||0), 0);
    const total = grandTotal();
    if (paid < total) { alert('المبلغ المدفوع أقل من الإجمالي'); return; }
    if (paymentRows.length === 0) { alert('أضف طريقة دفع واحدة على الأقل'); return; }

    fetch('process_sale.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            items: order,
            customer_id: document.getElementById('customerId').value,
            discount: parseFloat(document.getElementById('discount').value) || 0,
            payments: paymentRows
        })
    }).then(r => r.json()).then(data => {
        if (data.success) window.location.href = 'invoice.php?id=' + data.sale_id;
        else alert('خطأ: ' + data.message);
    });
}

renderPaymentRows();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
