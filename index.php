<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bookstore</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<?php include 'header.php'; ?>
<!-- ========== XU HƯỚNG - MÀU NỀN ĐẸP ========== -->
<?php
$sqlHot = "SELECT p.id, p.name, p.author, p.price, p.image_primary, COALESCE(SUM(s.quantity), 0) as sold
           FROM products p
           LEFT JOIN sales s ON p.id = s.product_id
           WHERE p.category != 'qua_tang'
           GROUP BY p.id
           ORDER BY sold DESC, p.id DESC
           LIMIT 6";
$hotProducts = $conn->query($sqlHot);
$productsArray = [];
if ($hotProducts) {
    while ($row = $hotProducts->fetch_assoc()) {
        $productsArray[] = $row;
    }
}
?>

<?php if (!empty($productsArray)): ?>
<!-- Full width background - MÀU XANH NHẠT -->
<div style="width: 100%; background: linear-gradient(90deg, #e8f0fe 0%, #f0f7ff 50%, #e8f0fe 100%); padding: 40px 0; margin: 30px 0; border-top: 1px solid #d4e2f0; border-bottom: 1px solid #d4e2f0;">
    
    <!-- Content container -->
    <div style="max-width: 1600px; margin: 0 auto; padding: 0 30px;">
        
        <!-- Tiêu đề -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2 style="margin: 0; color: #0a58ca; font-size: 28px; text-shadow: 0 1px 2px rgba(0,0,0,0.05);">🔥 XU HƯỚNG TUẦN QUA</h2>
            <a href="#" style="color: #0a58ca; text-decoration: none; font-size: 14px; font-weight: 500;">Xem tất cả →</a>
        </div>
        
        <!-- 3 cột -->
        <div style="display: flex; gap: 20px; align-items: stretch;">
            
            <!-- BANNER TRÁI -->
            <div class="banner-left" style="flex: 0 0 280px; background: linear-gradient(145deg, #1e3c72, #0f2a4f); border-radius: 24px; padding: 30px 20px; text-align: center; color: white; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <div style="font-size: 64px; margin-bottom: 20px;">📚</div>
                <div style="font-weight: bold; font-size: 24px; margin-bottom: 12px;">SÁCH MỚI</div>
                <div style="font-size: 15px; opacity: 0.9; margin-bottom: 10px;">Tuyển chọn đặc biệt</div>
                <div style="font-size: 13px; opacity: 0.8; margin-bottom: 25px;">Chỉ trong tháng 6 này </div>
                <div style="background: #ffc107; color: #1e3c72; padding: 10px 20px; border-radius: 40px; font-size: 15px; font-weight: bold; display: inline-block; align-self: center; width: fit-content;">
                    ⬇️ GIẢM 20%
                </div>
            </div>
            
            <!-- 6 SẢN PHẨM -->
            <div style="flex: 1; display: grid; grid-template-columns: repeat(6, 1fr); gap: 18px;">
                <?php $rank = 1; foreach ($productsArray as $item): 
                    $img = !empty($item['image_primary']) && file_exists('images/products/'.$item['image_primary']) 
                           ? 'images/products/'.$item['image_primary'] 
                           : 'images/banner.jpg';
                ?>
                <a href="product.php?id=<?= $item['id'] ?>" class="product-card" style="text-decoration: none; color: #333; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s ease; display: block;">
                    <div style="position: relative;">
                        <div style="position: absolute; top: 8px; left: 8px; background: <?= $rank <= 3 ? '#e74c3c' : '#0a58ca'; ?>; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px; z-index: 1; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            <?= $rank ?>
                        </div>
                        <?php if($rank <= 3): ?>
                        <div style="position: absolute; top: 8px; right: 8px; background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; padding: 3px 8px; border-radius: 20px; font-size: 10px; font-weight: bold; z-index: 1;">HOT</div>
                        <?php endif; ?>
                        <img src="<?= $img ?>" style="width: 100%; height: 190px; object-fit: contain; background: #fafafa; border-bottom: 1px solid #eee;">
                    </div>
                    <div style="padding: 10px;">
                        <div style="font-weight: 600; font-size: 13px; line-height: 1.4; height: 36px; overflow: hidden; margin-bottom: 5px;">
                            <?= htmlspecialchars(mb_substr($item['name'], 0, 35)) ?>
                        </div>
                        <div style="font-size: 11px; color: #888; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ✍️ <?= htmlspecialchars($item['author'] ?? 'Nhiều tác giả') ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
                            <span style="font-size: 11px; background: #e8f5e9; color: #2e7d32; padding: 2px 6px; border-radius: 20px;">
                                📊 <?= number_format($item['sold']) ?>
                            </span>
                            <span style="font-weight: 700; color: #d93025; font-size: 14px;">
                                <?= number_format($item['price'], 0, ',', '.') ?>đ
                            </span>
                        </div>
                    </div>
                </a>
                <?php $rank++; endforeach; ?>
            </div>
            
            <!-- BANNER PHẢI -->
            <div class="banner-right" style="flex: 0 0 280px; background: linear-gradient(145deg, #2d6a4f, #1b4d3e); border-radius: 24px; padding: 30px 20px; text-align: center; color: white; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <img src="images/gift.png" alt="Quà tặng" style="width: 80px; height: 80px; margin: 0 auto 20px; display: block; object-fit: contain;">
                <div style="font-weight: bold; font-size: 24px; margin-bottom: 12px;">QUÀ TẶNG</div>
                <div style="font-size: 15px; opacity: 0.9; margin-bottom: 10px;">Mua 2 cuốn bất kỳ</div>
                <div style="font-size: 13px; opacity: 0.8; margin-bottom: 25px;">Nhận ngay 1 móc khóa thổ cẩm </div>
                <div style="background: #ffc107; color: #2d6a4f; padding: 10px 20px; border-radius: 40px; font-size: 15px; font-weight: bold; display: inline-block; align-self: center; width: fit-content;">
                    🎁 NHẬN NGAY
                </div>
            </div>
            
        </div>
    </div>
</div>

<style>
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
}

/* Responsive */
@media (max-width: 1300px) {
    div[style*="display: flex; gap: 20px; align-items: stretch;"] {
        flex-wrap: wrap;
    }
    div[style*="flex: 1; display: grid;"] {
        grid-template-columns: repeat(3, 1fr) !important;
        order: 1;
        width: 100%;
        margin-top: 20px;
    }
    .banner-left, .banner-right {
        flex: 1 !important;
        min-width: 220px;
    }
}

@media (max-width: 768px) {
    div[style*="flex: 1; display: grid;"] {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
    }
    div[style*="max-width: 1600px;"] {
        padding: 0 15px !important;
    }
}
</style>
<?php endif; ?>
<div class="content-area">

<!-- 📦 SẢN PHẨM (CHỈ SÁCH) -->
<?php
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$conditions = [];
if ($category !== '') {
    $conditions[] = "category = '" . $conn->real_escape_string($category) . "'";
}
if ($search !== '') {
    $searchTerm = '%' . $conn->real_escape_string($search) . '%';
    $conditions[] = "(name LIKE '$searchTerm' OR description LIKE '$searchTerm')";
}

// 👇 SỬA DÒNG NÀY: Chỉ lấy SÁCH (không bao gồm ấn phẩm)
$sql = "SELECT * FROM products WHERE category NOT IN ('an_pham_van_hoa', 'qua_tang')";

if (!empty($conditions)) {
    $sql .= ' AND ' . implode(' AND ', $conditions);
}

$heading = 'Danh sách sản phẩm';
if ($search !== '') {
    $heading = 'Kết quả tìm kiếm cho "' . htmlspecialchars($search) . '"';
}
?>

<h2 class="title">📚 <?= $heading ?></h2>

<div class="product-list">
<?php
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
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
?>
</div>

    <!-- ========== PHẦN ẤN PHẨM VĂN HÓA ========== -->
    <h2 class="title">🎨 ẤN PHẨM VĂN HÓA</h2>
    <div class="product-list">
        <?php
        // Lấy sản phẩm thuộc danh mục Ấn Phẩm Văn Hóa
        $sqlAnPham = "SELECT * FROM products 
                      WHERE category IN ('an_pham_van_hoa', 'qua_tang') 
                      ORDER BY id DESC";
                      
        $resultAnPham = $conn->query($sqlAnPham);
        
        if ($resultAnPham && $resultAnPham->num_rows > 0) {
            while($row = $resultAnPham->fetch_assoc()) {
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
                echo "<div class='product-meta'>" . htmlspecialchars(!empty($row['author']) ? $row['author'] : 'Ấn phẩm') . "</div>";
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
            echo '<p style="text-align:center; color:#999;">🎁 Chưa có ấn phẩm văn hóa nào.</p>';
        }
        ?>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>