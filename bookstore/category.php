<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bookstore - Danh mục</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<?php
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sub = isset($_GET['sub']) ? $_GET['sub'] : '';
$categories = [
    'sach_vietnam' => 'Sách Việt Nam',
    'sach_nuoc_ngoai' => 'Foreign Books',
    'van_phong_pham' => 'Văn Phòng Phẩm',
    'do_choi' => 'Đồ Chơi',
    'qua_tang' => 'Quà Tặng'
];
$subNames = [
    'bup_be' => 'Búp Bê - Thú Bông',
    'do_choi_xe_may_bay' => 'Đồ Chơi Xe, Máy Bay',
    'robot_sieu_nhan' => 'Robot - Siêu Nhân',
    'board_game' => 'Board Game',
    'do_choi_van_dong' => 'Đồ Chơi Vận Động',
    'do_choi_giao_duc' => 'Đồ Chơi Giáo Dục',
    'hobby' => 'Hobby',
    'tieu_thuyet' => 'Tiểu Thuyết',
    'kinh_doanh' => 'Kinh Doanh',
    'khoa_hoc' => 'Khoa Học',
    'but' => 'Bút',
    'so_tay' => 'Sổ Tay',
    'tui_dung' => 'Túi Đựng',
    'do_choi_tre_em' => 'Đồ Chơi Trẻ Em',
    'do_choi_ngoai_troi' => 'Đồ Chơi Ngoài Trời',
    'gift_set' => 'Gift Set',
    'qua_sinh_nhat' => 'Quà Sinh Nhật',
    'qua_luu_niem' => 'Quà Lưu Niệm'
];

if (!isset($categories[$category])) {
    echo '<h2 class="title">Danh mục không tồn tại</h2>';
    echo '<div class="product-list"><p>Vui lòng chọn lại danh mục.</p></div>';
    exit;
}

$categoryName = $categories[$category];
$subTitle = isset($subNames[$sub]) ? ' / ' . $subNames[$sub] : '';
$selectedCategory = $conn->real_escape_string($category);
$sql = "SELECT * FROM products WHERE category = '$selectedCategory'";
$result = $conn->query($sql);
?>

<div class="content-area">
    <h2 class="title">Danh mục: <?php echo $categoryName . $subTitle; ?></h2>

    <div class="product-list">
<?php
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imagePath = !empty($row['image']) && file_exists(__DIR__ . '/images/products/' . $row['image'])
            ? 'images/products/' . htmlspecialchars($row['image'])
            : 'images/banner.jpg';
        echo "<a class='product-link' href='product.php?id=" . $row['id'] . "'>";
        echo "<div class='product'>";
        echo "<div class='product-image'>";
        echo "<img src='" . $imagePath . "' alt='" . htmlspecialchars($row['name']) . "'>";
        echo "</div>";
        echo "<div class='product-body'>";
        echo "<h3 class='product-title'>" . htmlspecialchars($row['name']) . "</h3>";
        echo "<div class='product-meta'>" . htmlspecialchars(!empty($row['author']) ? $row['author'] : $row['category']) . "</div>";
        echo "<div class='product-price'>";
        echo "<span class='price'>" . number_format($row['price'], 0, ',', '.') . "đ</span>";
        echo "</div>";
        echo "<div class='product-actions'>";
        echo "<a href='product.php?id=" . $row['id'] . "' class='quick-view'>→</a>";
        echo "<span class='buy-now'>XEM NHANH</span>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</a>";
    }
} else {
    echo '<p>Không có sản phẩm trong danh mục này.</p>';
}
?>
    </div>
</div>

</body>
</html>
