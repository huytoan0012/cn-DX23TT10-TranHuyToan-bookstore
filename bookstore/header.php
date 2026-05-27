<?php

$user = current_user();
$cartCount = cart_count();
$searchValue = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
// KIỂM TRA SESSION - XÓA SAU KHI XONG

?>

<!-- 🔵 BANNER -->
<div class="top-banner">
    <img src="images/banner.jpg" alt="Banner">
</div>

<!-- 🔷 HEADER -->
<div class="header">
    <div class="logo">
        <a href="index.php">
            <img src="images/logo.png" alt="Logo">
            <span>NHÀ SÁCH Á ĐÔNG</span>
        </a>
    </div>

    <form action="index.php" method="get" class="search-box" style="margin:0; display:flex;">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?= $searchValue ?>" class="search">
    </form>

    <div class="header-actions">
        <?php if ($user): ?>
            <span class="action-link login-link">
                <span class="action-icon">👤</span>
                Xin chào, <?= htmlspecialchars($user['username']) ?>
            </span>
            <a href="logout.php" class="action-link login-link">Đăng xuất</a>
        <?php else: ?>
            <a href="login.php" class="action-link login-link">
                <span class="action-icon">👤</span>
                Đăng nhập
            </a>
        <?php endif; ?>
        <a href="cart.php" class="action-link cart-link">
            <span class="action-icon">🛒</span>
            Giỏ hàng (<?= $cartCount ?>)
        </a>
    </div>
</div>

<!-- 🟦 MENU -->
<div class="menu">
    <div class="menu-item">
        <a href="index.php">Trang chủ</a>
    </div>
    <?php if (is_logged_in() && is_admin()): ?>
<div class="menu-item">
    <a href="admin_stats.php">📊 Thống kê doanh thu</a>
</div>
<?php endif; ?>
    <div class="menu-item has-submenu">
        <a href="category.php?category=sach_vietnam">Sách Khảo Cứu & Di Sản</a>
        <div class="submenu">
            <a href="category.php?category=sach_vietnam&sub=bup_be">Búp Bê - Thú Bông</a>
            <a href="category.php?category=sach_vietnam&sub=do_choi_xe_may_bay">Đồ Chơi Xe, Máy Bay</a>
            <a href="category.php?category=sach_vietnam&sub=robot_sieu_nhan">Robot - Siêu Nhân</a>
            <a href="category.php?category=sach_vietnam&sub=board_game">Board Game</a>
            <a href="category.php?category=sach_vietnam&sub=do_choi_van_dong">Đồ Chơi Vận Động</a>
            <a href="category.php?category=sach_vietnam&sub=do_choi_giao_duc">Đồ Chơi Giáo Dục</a>
            <a href="category.php?category=sach_vietnam&sub=hobby">Hobby</a>
        </div>
    </div>
    <div class="menu-item has-submenu">
        <a href="category.php?category=sach_nuoc_ngoai">Nghệ Thuật & Kiến Trúc Việt</a>
        <div class="submenu">
            <a href="category.php?category=sach_nuoc_ngoai&sub=tieu_thuyet">Tiểu Thuyết</a>
            <a href="category.php?category=sach_nuoc_ngoai&sub=kinh_doanh">Kinh Doanh</a>
            <a href="category.php?category=sach_nuoc_ngoai&sub=khoa_hoc">Khoa Học</a>
        </div>
    </div>
    <div class="menu-item has-submenu">
        <a href="category.php?category=van_phong_pham">Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ</a>
        <div class="submenu">
            <a href="category.php?category=van_phong_pham&sub=but">Bút</a>
            <a href="category.php?category=van_phong_pham&sub=so_tay">Sổ Tay</a>
            <a href="category.php?category=van_phong_pham&sub=tui_dung">Túi Đựng</a>
        </div>
    </div>
    <div class="menu-item has-submenu">
        <a href="category.php?category=do_choi">Văn Hóa Ẩm Thực & Phong Vị Bản Địa</a>
        <div class="submenu">
            <a href="category.php?category=do_choi&sub=do_choi_tre_em">Đồ Chơi Trẻ Em</a>
            <a href="category.php?category=do_choi&sub=do_choi_giao_duc">Đồ Chơi Giáo Dục</a>
            <a href="category.php?category=do_choi&sub=do_choi_ngoai_troi">Đồ Chơi Ngoài Trời</a>
        </div>
    </div>
    <div class="menu-item has-submenu">
        <a href="category.php?category=qua_tang">Ấn Phẩm Văn Hóa</a>
        <div class="submenu">
            <a href="category.php?category=qua_tang&sub=gift_set">Gift Set</a>
            <a href="category.php?category=qua_tang&sub=qua_sinh_nhat">Quà Sinh Nhật</a>
            <a href="category.php?category=qua_tang&sub=qua_luu_niem">Quà Lưu Niệm</a>
        </div>
    </div>
</div>

<!-- Scroll to Top Button -->
<button id="scroll-to-top" class="scroll-to-top">↑</button>

<!-- Floating Cart Icon -->
<div class="floating-cart">
    <a href="cart.php">
        <span class="cart-icon">🛒</span>
        <span class="cart-count"><?= $cartCount ?></span>
    </a>
</div>

<!-- Quick View Modal -->
<div id="quick-view-modal" class="qv-modal" aria-hidden="true">
    <div class="qv-backdrop"></div>
    <div class="qv-panel" role="dialog" aria-modal="true">
        <button class="qv-close" aria-label="Đóng">✕</button>
        <div id="qv-content">Loading…</div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scroll-to-top');
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('show');
        } else {
            scrollToTopBtn.classList.remove('show');
        }
    });
    
    // Scroll to top on click
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Quick view modal logic
    const qvModal = document.getElementById('quick-view-modal');
    const qvContent = document.getElementById('qv-content');
    const qvClose = qvModal.querySelector('.qv-close');

    function openQuickView(id) {
        qvModal.setAttribute('aria-hidden', 'false');
        qvContent.innerHTML = 'Đang tải...';
        fetch('quick_view.php?id=' + encodeURIComponent(id))
            .then(r => {
                if (!r.ok) throw new Error('Không tải được sản phẩm');
                return r.text();
            })
            .then(html => qvContent.innerHTML = html)
            .catch(err => qvContent.innerHTML = '<p>Lỗi: ' + err.message + '</p>');
    }

    function closeQuickView() {
        qvModal.setAttribute('aria-hidden', 'true');
        qvContent.innerHTML = '';
    }

    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.quick-view-btn');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.getAttribute('data-id');
            openQuickView(id);
        }
        if (e.target.matches('.qv-backdrop') || e.target.closest('.qv-close')) {
            closeQuickView();
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && qvModal.getAttribute('aria-hidden') === 'false') closeQuickView();
    });
});
</script>
