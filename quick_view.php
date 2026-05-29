<?php
include "config.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo '<p>Sản phẩm không hợp lệ</p>';
    exit;
}

$sql = "SELECT * FROM products WHERE id = $id";
$result = $conn->query($sql);
$product = $result->fetch_assoc();

if (!$product) {
    echo '<p>Không tìm thấy sản phẩm</p>';
    exit;
}

// ƯU TIÊN LẤY ẢNH TỪ image_primary (cột mới)
$imageName = '';

if (!empty($product['image_primary'])) {
    $imageName = $product['image_primary'];
} elseif (!empty($product['image'])) {
    $imageName = $product['image'];
}

// Kiểm tra file ảnh có tồn tại không
$imagePath = 'images/banner.jpg'; // ảnh mặc định
if (!empty($imageName) && file_exists(__DIR__ . '/images/products/' . $imageName)) {
    $imagePath = 'images/products/' . $imageName;
}

// Xử lý tên danh mục
$categoryNames = [
    'sach_vietnam' => 'Sách Khảo Cứu & Di Sản',
    'sach_nuoc_ngoai' => 'Nghệ Thuật & Kiến Trúc Việt',
    'van_phong_pham' => 'Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ',
    'do_choi' => 'Văn Hóa Ẩm Thực & Phong Vị Bản Địa',
    'qua_tang' => 'Ấn Phẩm Văn Hóa'
];
$categoryName = $categoryNames[$product['category']] ?? $product['category'];
?>

<div style="display: flex; gap: 20px; flex-wrap: wrap; font-family: Arial, sans-serif;">
    <!-- Cột ảnh bên trái -->
    <div style="flex: 1; min-width: 200px; text-align: center;">
        <img src="<?= $imagePath ?>" 
             alt="<?= htmlspecialchars($product['name']) ?>" 
             style="width: 100%; max-width: 250px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    </div>
    
    <!-- Cột thông tin bên phải -->
    <div style="flex: 2;">
        <h3 style="margin-top: 0; color: #333;"><?= htmlspecialchars($product['name']) ?></h3>
        
        <p style="color: #666; font-size: 14px; margin: 5px 0;">
            <strong>Danh mục:</strong> <?= $categoryName ?>
        </p>
        
        <p style="color: #d93025; font-size: 28px; font-weight: bold; margin: 15px 0;">
            <?= number_format($product['price'], 0, ',', '.') ?>đ
        </p>
        
        <p style="margin: 10px 0;">
            <strong>✍️ Tác giả:</strong> <?= htmlspecialchars($product['author'] ?? 'Chưa có') ?>
        </p>
        
        <p style="margin: 10px 0;">
            <strong>🏛️ Nhà xuất bản:</strong> <?= htmlspecialchars($product['publisher'] ?? 'Chưa có') ?>
        </p>
        
        <p style="margin: 10px 0;">
            <strong>📦 Tình trạng:</strong> 
            <span style="color: <?= ($product['stock'] > 0) ? '#28a745' : '#dc3545' ?>; font-weight: bold;">
                <?= ($product['stock'] > 0) ? 'Còn hàng (' . $product['stock'] . ' sản phẩm)' : 'Hết hàng' ?>
            </span>
        </p>
        
        <div style="background: #f5f5f5; padding: 12px; border-radius: 8px; margin: 15px 0;">
            <strong>📖 Mô tả:</strong>
            <p style="margin: 8px 0 0 0; line-height: 1.5; color: #555;">
                <?= nl2br(htmlspecialchars(mb_substr($product['description'] ?? '', 0, 300))) ?>
                <?php if (strlen($product['description'] ?? '') > 300): ?>...<?php endif; ?>
            </p>
        </div>
        
        <form method="post" action="cart.php" style="margin-top: 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" 
                   style="width: 70px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; text-align: center;">
            <button type="submit" name="add_to_cart" 
                    style="background: #0a58ca; color: white; padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
                🛒 Thêm vào giỏ
            </button>
            <a href="product.php?id=<?= $product['id'] ?>" 
               style="background: #f0f0f0; color: #333; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
                📖 Xem chi tiết
            </a>
        </form>
    </div>
</div>