<?php
include 'config.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo 'Missing id';
    exit;
}
$id = intval($_GET['id']);
$sql = "SELECT * FROM products WHERE id = $id LIMIT 1";
$res = $conn->query($sql);
if (!$res || $res->num_rows === 0) {
    http_response_code(404);
    echo 'Product not found';
    exit;
}
$product = $res->fetch_assoc();
// return fragment HTML
$image = (!empty($product['image']) && file_exists(__DIR__ . '/images/products/' . $product['image'])) ? 'images/products/' . htmlspecialchars($product['image']) : 'images/banner.jpg';
?>
<div class="quick-view-content">
    <div class="qv-image"><img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>"></div>
    <div class="qv-info">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <div class="qv-meta"><?= htmlspecialchars(!empty($product['author']) ? $product['author'] : $product['category']) ?></div>
        <div class="qv-price"><?= number_format($product['price'],0,',','.') ?> đ</div>
        <div class="qv-desc"><?= nl2br(htmlspecialchars($product['description'] ?: 'Chưa có mô tả')) ?></div>
        <form method="post" action="product.php?id=<?= $product['id'] ?>">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <label>Số lượng: <input type="number" name="quantity" value="1" min="1" style="width:60px"></label>
            <button type="submit" name="add_to_cart" class="btn-primary" style="margin-left:10px">Thêm vào giỏ</button>
        </form>
    </div>
</div>