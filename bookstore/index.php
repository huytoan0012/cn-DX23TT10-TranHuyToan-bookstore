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
<div class="content-area">

<!-- 📦 SẢN PHẨM -->
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

$sql = "SELECT * FROM products";
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$heading = 'Danh sách sản phẩm';
if ($search !== '') {
    $heading = 'Kết quả tìm kiếm cho "' . htmlspecialchars($search) . '"';
}
?>

<h2 class="title"><?= $heading ?></h2>

<div class="product-list">
<?php
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
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
?>
</div>
</div>

</body>
</html>