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

// Ánh xạ từ mã danh mục (trong URL và database) sang tên hiển thị
$categories = [
    'sach_khao_cuu_va_di_san' => 'Sách Khảo Cứu & Di Sản',
    'nghe_thuat_va_kien_truc_viet' => 'Nghệ Thuật & Kiến Trúc Việt',
    'van_hoc_va_tinh_hoa_nghe_thuat_ngon_tu' => 'Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ',
    'van_hoa_am_thuc_va_phong_vi' => 'Văn Hóa Ẩm Thực & Phong Vị Bản Địa',
    'an_pham_van_hoa' => 'Ấn Phẩm Văn Hóa'
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
        $imagePath = !empty($row['image_primary']) && file_exists(__DIR__ . '/images/products/' . $row['image_primary'])
            ? 'images/products/' . htmlspecialchars($row['image_primary'])
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
        echo "<button class='quick-view-btn' data-id='" . $row['id'] . "'>XEM NHANH</button>";
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
<?php include 'footer.php'; ?>
</body>
</html>