// تفاعلات بسيطة محلية (بديل Bootstrap JS) — تعمل بدون إنترنت بالكامل
document.addEventListener('click', function (e) {
    const toggler = e.target.closest('.navbar-toggler');
    if (toggler) {
        document.getElementById('navMenu')?.classList.toggle('show');
        return;
    }
    const dropToggle = e.target.closest('.dropdown-toggle');
    if (dropToggle) {
        e.preventDefault();
        const menu = dropToggle.nextElementSibling;
        document.querySelectorAll('.dropdown-menu.show').forEach(m => { if (m !== menu) m.classList.remove('show'); });
        menu?.classList.toggle('show');
        return;
    }
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
    }
});
