<?php include "config.php"; ?>

<?php
// Xử lý thêm vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'], $_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    if ($productId > 0) {
        $sql = "SELECT stock FROM products WHERE id = $productId";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            $currentStock = $row['stock'];
            $currentInCart = $_SESSION['cart'][$productId] ?? 0;
            if ($currentInCart + $quantity <= $currentStock) {
                if (!isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] = 0;
                }
                $_SESSION['cart'][$productId] += $quantity;
                header('Location: cart.php');
                exit;
            } else {
                $error = "Không đủ hàng. Tồn kho: $currentStock, trong giỏ: $currentInCart";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm - Nhà Sách Á Đông</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ========== GIỮ NGUYÊN CSS GỐC TỪ PRODUCT.PHP CŨ ========== */
        .product-detail {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 18px rgba(0,0,0,0.08);
        }

        .breadcrumb {
            margin-bottom: 20px;
            color: #555;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #0a58ca;
            text-decoration: none;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
            align-items: start;
        }

        .detail-image {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            background: #f7f7f7;
            text-align: center;
        }

        .detail-image img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        /* Gallery ảnh nhỏ */
        .detail-thumbnails {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .thumbnail {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .thumbnail:hover {
            border-color: #0a58ca;
            transform: scale(1.05);
        }

        .detail-info h1 {
            margin-top: 0;
            font-size: 28px;
            line-height: 1.3;
            color: #333;
        }

        .detail-price {
            font-size: 28px;
            color: #d93025;
            font-weight: 700;
            margin: 22px 0 12px;
        }

        .detail-description {
            line-height: 1.8;
            color: #444;
            margin: 18px 0;
        }

        .detail-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .detail-author-publisher {
            display: grid;
            gap: 12px;
            margin: 20px 0;
            padding: 18px;
            background: #f4f7ff;
            border-radius: 14px;
            color: #333;
            line-height: 1.6;
            border: 1px solid rgba(10, 88, 202, 0.12);
        }

        .detail-stock {
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            margin: 10px 0;
        }

        .stock-in {
            background: #d4edda;
            color: #155724;
        }

        .stock-out {
            background: #f8d7da;
            color: #721c24;
        }

        .detail-shipping {
            margin-top: 30px;
            padding: 24px;
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid #e6eaf3;
            box-shadow: 0 1px 10px rgba(0,0,0,0.04);
        }

        .shipping-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #102a5f;
        }

        .shipping-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 12px;
            color: #555;
        }

        .shipping-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding-left: 2px;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #0a58ca;
            color: white;
        }

        .btn-primary:hover {
            background: #0a4fa1;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f4f6f9;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* ========== SÁCH GỢI Ý ========== */
        .related-products {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #eaeaea;
        }

        .related-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 25px;
            padding-left: 10px;
            border-left: 5px solid #0a58ca;
        }

        .related-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .related-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #eee;
            display: block;
        }

        .related-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .related-img {
            width: 100%;
            height: 180px;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .related-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s;
        }

        .related-item:hover .related-img img {
            transform: scale(1.05);
        }

        .related-info {
            padding: 12px;
        }

        .related-name {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 8px;
            color: #333;
            min-height: 40px;
        }

        .related-price {
            font-size: 16px;
            font-weight: 700;
            color: #d93025;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-detail {
                padding: 15px;
            }
            .detail-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .related-list {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
            }
            .related-img {
                height: 140px;
            }
            .related-name {
                font-size: 12px;
            }
            .related-price {
                font-size: 14px;
            }
            .thumbnail {
                width: 55px;
                height: 55px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<?php
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($id > 0) {
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}

$categoryNames = [
    'sach_vietnam' => 'Sách Khảo Cứu & Di Sản',
    'sach_nuoc_ngoai' => 'Nghệ Thuật & Kiến Trúc Việt',
    'van_phong_pham' => 'Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ',
    'do_choi' => 'Văn Hóa Ẩm Thực & Phong Vị Bản Địa',
    'qua_tang' => 'Ấn Phẩm Văn Hóa'
];
?>

<div class="product-detail">
    <?php if (!$product): ?>
        <div class="error-message">Sản phẩm không tồn tại hoặc đã bị xóa.</div>
        <div style="text-align:center; margin-top:20px;"><a href="index.php" class="btn-secondary">← Quay lại trang chủ</a></div>
    <?php else: ?>
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a> /
            <a href="category.php?category=<?php echo htmlspecialchars($product['category']); ?>"><?php echo htmlspecialchars($categoryNames[$product['category']] ?? $product['category']); ?></a> /
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <div class="detail-grid">
            <!-- Cột ảnh + gallery -->
            <div class="detail-gallery">
                <div class="detail-image">
                    <?php 
                    $primary_image = !empty($product['image_primary']) ? $product['image_primary'] : ($product['image'] ?? 'banner.jpg');
                    ?>
                    <img id="main-image" src="images/products/<?php echo htmlspecialchars($primary_image); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <?php 
                $images_array = [];
                if (!empty($product['images'])) {
                    $images_array = explode(',', $product['images']);
                }
                if (count($images_array) > 0):
                ?>
                <div class="detail-thumbnails">
                    <?php foreach($images_array as $thumb): 
                        $thumb = trim($thumb);
                        if (empty($thumb)) continue;
                    ?>
                        <img src="images/products/<?php echo htmlspecialchars($thumb); ?>" 
                             class="thumbnail" 
                             onclick="document.getElementById('main-image').src = this.src"
                             onerror="this.style.display='none'">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Cột thông tin -->
            <div class="detail-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="detail-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</div>
                
                <div class="detail-author-publisher">
                    <div><strong>✍️ Tác giả:</strong> <?php echo htmlspecialchars(!empty($product['author']) ? $product['author'] : 'Chưa có'); ?></div>
                    <div><strong>🏛️ Nhà xuất bản:</strong> <?php echo htmlspecialchars(!empty($product['publisher']) ? $product['publisher'] : 'Chưa có'); ?></div>
                </div>
                
                <div class="detail-description"><?php echo nl2br(htmlspecialchars($product['description'] ?: 'Chưa có mô tả chi tiết cho sản phẩm này.')); ?></div>
                
                <div class="detail-stock <?php echo ($product['stock'] > 0) ? 'stock-in' : 'stock-out'; ?>">
                    📦 Tình trạng: <?php echo ($product['stock'] > 0) ? 'Còn hàng (' . $product['stock'] . ' sản phẩm)' : 'Hết hàng'; ?>
                </div>
                
                <?php if (isset($error)): ?>
                    <div style="color: red; margin: 10px 0;"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="detail-actions">
                    <?php if ($product['stock'] > 0): ?>
                    <form method="post" action="product.php?id=<?php echo $product['id']; ?>" style="margin:0;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" name="add_to_cart" class="btn-primary">🛒 Thêm vào giỏ</button>
                    </form>
                    <?php endif; ?>
                    <a href="index.php" class="btn-secondary">📖 Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>

        <!-- PHẦN SÁCH GỢI Ý (LIÊN QUAN) -->
        <?php
        $currentCategory = $conn->real_escape_string($product['category']);
        $currentId = $product['id'];

        $sqlRelated = "SELECT * FROM products 
                       WHERE category = '$currentCategory' 
                       AND id != $currentId 
                       AND stock > 0
                       ORDER BY RAND() 
                       LIMIT 6";
        $relatedResult = $conn->query($sqlRelated);

        if ($relatedResult && $relatedResult->num_rows > 0):
        ?>
        <div class="related-products">
            <h3 class="related-title">📚 Sách gợi ý cho bạn</h3>
            <div class="related-list">
                <?php while($related = $relatedResult->fetch_assoc()): 
                    $relImage = !empty($related['image_primary']) && file_exists(__DIR__ . '/images/products/' . $related['image_primary'])
                        ? 'images/products/' . htmlspecialchars($related['image_primary'])
                        : 'images/banner.jpg';
                ?>
                <a href="product.php?id=<?= $related['id'] ?>" class="related-item">
                    <div class="related-img">
                        <img src="<?= $relImage ?>" alt="<?= htmlspecialchars($related['name']) ?>">
                    </div>
                    <div class="related-info">
                        <div class="related-name"><?= htmlspecialchars(mb_substr($related['name'], 0, 40)) ?>...</div>
                        <div class="related-price"><?= number_format($related['price'], 0, ',', '.') ?>đ</div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<script>
function changeImage(src) {
    document.getElementById('main-image').src = src;
}
</script>

</body>
</html>