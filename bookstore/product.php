<?php include "config.php"; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'], $_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    if ($productId > 0) {
        if (!isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = 0;
        }
        $_SESSION['cart'][$productId] += $quantity;
        header('Location: cart.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .product-detail {
            max-width: 1100px;
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
            grid-template-columns: 1.05fr 1fr;
            gap: 30px;
            align-items: start;
        }

        .detail-image {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            background: #f7f7f7;
        }

        .detail-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .detail-info h1 {
            margin-top: 0;
            font-size: 32px;
            line-height: 1.2;
        }

        .detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin: 16px 0;
            color: #555;
        }

        .detail-meta span {
            background: #f3f6ff;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 14px;
            color: #0a58ca;
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
            padding: 14px 22px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
        }

        .btn-primary {
            background: #0a58ca;
            color: white;
        }

        .btn-secondary {
            background: #f4f6f9;
            color: #333;
        }

        .detail-note {
            margin-top: 20px;
            padding: 18px;
            border-radius: 12px;
            background: #f0f4ff;
            color: #2e4a9a;
            border: 1px solid rgba(10,88,202,0.16);
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
    'khao_cuu_van_hoa' => 'Khảo cứu Văn hóa & Phong tục Học',
    'lich_su_do_thi' => 'Lịch sử & Di sản Đô thị',
    'nghe_thuat_kien_truc' => 'Nghệ thuật, Kiến trúc & Trang phục',
    'am_thuc_van_chuong' => 'Văn hóa Âm thực & Văn chương Lối sống',
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
            <div class="detail-image">
                <?php if (!empty($product['image']) && file_exists(__DIR__ . '/images/products/' . $product['image'])): ?>
                    <img src="images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <img src="images/banner.jpg" alt="Không có ảnh">
                <?php endif; ?>
            </div>
            <div class="detail-info">
                <div class="detail-summary">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="detail-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VND</div>
                    <div class="detail-author-publisher">
                        <div><strong>Tác giả:</strong> <?php echo htmlspecialchars(!empty($product['author']) ? $product['author'] : 'Chưa có'); ?></div>
                        <div><strong>Nhà xuất bản:</strong> <?php echo htmlspecialchars(!empty($product['publisher']) ? $product['publisher'] : 'Chưa có'); ?></div>
                    </div>
                    <div class="detail-description"><?php echo nl2br(htmlspecialchars($product['description'] ?: 'Chưa có mô tả chi tiết cho sản phẩm này.')); ?></div>
                    <div class="detail-actions">
                        <form method="post" action="product.php?id=<?php echo $product['id']; ?>" style="margin:0;">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" name="add_to_cart" class="btn-primary">Thêm vào giỏ</button>
                        </form>
                        <a href="index.php" class="btn-secondary">Tiếp tục mua sắm</a>
                    </div>
                </div>
                <div class="detail-shipping">
                    <div class="shipping-title">Thông tin vận chuyển</div>
                    <ul class="shipping-list">
                        <li>🚚 Giao hàng tiêu chuẩn: 2-4 ngày làm việc.</li>
                        <li>🏷️ Miễn phí giao hàng cho đơn hàng trên 200.000 VND.</li>
                        <li>🔄 Đổi trả miễn phí trong 7 ngày nếu lỗi do nhà cung cấp.</li>
                        <li>📦 Xử lý đơn hàng nhanh chóng và đóng gói cẩn thận.</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
