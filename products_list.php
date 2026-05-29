<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sản Phẩm</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .products-container {
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .add-btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .add-btn:hover {
            background: #218838;
        }

        .import-form {
            background: #f0f7ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #cce5ff;
        }

        .import-form h3 {
            margin: 0 0 15px 0;
            color: #0a58ca;
        }

        .import-form select,
        .import-form input {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .import-form select {
            min-width: 300px;
        }

        .import-form input {
            width: 150px;
        }

        .btn-import {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-import:hover {
            background: #218838;
        }

        .stats-section {
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }

        .stat-card h4 {
            margin: 0 0 10px 0;
            color: #555;
            font-size: 14px;
        }

        .stat-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #0a58ca;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #f0f0f0;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit {
            background: #0a58ca;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }

        .btn-edit:hover {
            background: #0a4fa1;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .stock-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .stock-high {
            background: #d4edda;
            color: #155724;
        }

        .stock-low {
            background: #fff3cd;
            color: #856404;
        }

        .stock-zero {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<!-- BANNER -->
<div class="top-banner">
    <img src="images/banner.jpg" alt="Banner">
</div>

<!-- HEADER -->
<div class="header">
    <div class="logo">
        <a href="index.php">
            <img src="images/logo.png" alt="Logo">
            <span>NHÀ SÁCH Á ĐÔNG</span>
        </a>
    </div>

    <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Tìm kiếm sản phẩm..." class="search">
    </div>

    <div class="header-actions">
        <?php if (is_logged_in()): ?>
            <span class="action-link">
                👤 Xin chào, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
            </span>
            <a href="logout.php" class="action-link">Đăng xuất</a>
        <?php else: ?>
            <a href="login.php" class="action-link">👤 Đăng nhập</a>
        <?php endif; ?>
        <a href="cart.php" class="action-link">🛒 Giỏ hàng</a>
    </div>
</div>

<div class="products-container">
    <h2>
        📦 Quản Lý Sản Phẩm
        <a href="add_product.php" class="add-btn">➕ Thêm Sản Phẩm</a>
    </h2>

    <?php
    // Xử lý nhập kho nhanh
    if (isset($_POST['update_stock']) && isset($_POST['product_id']) && isset($_POST['stock_quantity'])) {
        $product_id = intval($_POST['product_id']);
        $add_quantity = intval($_POST['stock_quantity']);
        
        if ($add_quantity > 0 && $product_id > 0) {
            $conn->query("UPDATE products SET stock = stock + $add_quantity WHERE id = $product_id");
            $success_message = "✅ Đã nhập thêm $add_quantity sản phẩm!";
        } else {
            $error_message = "⚠️ Vui lòng chọn sản phẩm và nhập số lượng hợp lệ!";
        }
    }

    // Xử lý xóa sản phẩm
    if (isset($_GET['delete']) && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        
        // Lấy tên ảnh để xóa
        $img_result = $conn->query("SELECT image_primary, images FROM products WHERE id = $id");
        if ($img_result && $row_img = $img_result->fetch_assoc()) {
            // Xóa ảnh chính
            if (!empty($row_img['image_primary']) && file_exists('images/products/' . $row_img['image_primary'])) {
                unlink('images/products/' . $row_img['image_primary']);
            }
            // Xóa ảnh phụ
            if (!empty($row_img['images'])) {
                $images_arr = explode(',', $row_img['images']);
                foreach ($images_arr as $img) {
                    if (file_exists('images/products/' . trim($img))) {
                        unlink('images/products/' . trim($img));
                    }
                }
            }
        }
        
        $sql = "DELETE FROM products WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            $success_message = '✅ Xóa sản phẩm thành công!';
        } else {
            $error_message = '❌ Lỗi: ' . $conn->error;
        }
    }

    if (isset($_GET['added'])) {
        $success_message = '✅ Thêm sản phẩm thành công!';
    }
    ?>

    <?php if (isset($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Form nhập kho nhanh -->
    <div class="import-form">
        <h3>📦 Nhập kho nhanh</h3>
        <form method="post" action="products_list.php">
            <select name="product_id" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php
                $allProducts = $conn->query("SELECT id, name, stock FROM products ORDER BY name");
                while($p = $allProducts->fetch_assoc()):
                ?>
                <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars(mb_substr($p['name'], 0, 50)) ?> (Tồn: <?= $p['stock'] ?>)
                </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="stock_quantity" placeholder="Số lượng nhập thêm" min="1" required>
            <button type="submit" name="update_stock" class="btn-import">➕ Nhập kho</button>
        </form>
    </div>

    <?php
    // Thống kê
    $totalProducts = 0;
    $totalStockValue = 0;
    $totalRevenue = 0;
    
    $countResult = $conn->query("SELECT COUNT(*) as count FROM products");
    if ($countResult) {
        $countRow = $countResult->fetch_assoc();
        $totalProducts = $countRow['count'];
    }
    
    $stockResult = $conn->query("SELECT SUM(price * stock) as value FROM products");
    if ($stockResult) {
        $stockRow = $stockResult->fetch_assoc();
        $totalStockValue = $stockRow['value'] ?? 0;
    }
    
    $revenueResult = $conn->query("SELECT SUM(total_price) as revenue FROM sales");
    if ($revenueResult) {
        $revenueRow = $revenueResult->fetch_assoc();
        $totalRevenue = $revenueRow['revenue'] ?? 0;
    }
    ?>

    <div class="stats-section">
        <h3>📊 Thống Kê</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Tổng Sản Phẩm</h4>
                <p><?php echo $totalProducts; ?></p>
            </div>
            <div class="stat-card">
                <h4>Giá Trị Tồn Kho</h4>
                <p><?php echo number_format($totalStockValue, 0, ',', '.'); ?> VND</p>
            </div>
            <div class="stat-card">
                <h4>Tổng Doanh Thu</h4>
                <p><?php echo number_format($totalRevenue, 0, ',', '.'); ?> VND</p>
            </div>
        </div>
    </div>

    <?php
    $sql = "SELECT id, name, price, category, image_primary, stock FROM products ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0):
    ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên Sản Phẩm</th>
                <th>Giá (VND)</th>
                <th>Danh Mục</th>
                <th>Tồn Kho</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Mảng chuyển đổi mã danh mục thành tên hiển thị
            $categoryNames = [
                'sach_vietnam' => 'Sách Khảo Cứu & Di Sản',
                'sach_nuoc_ngoai' => 'Nghệ Thuật & Kiến Trúc Việt',
                'van_phong_pham' => 'Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ',
                'do_choi' => 'Văn Hóa Ẩm Thực & Phong Vị Bản Địa',
                'qua_tang' => 'Ấn Phẩm Văn Hóa'
            ];
            
            while ($row = $result->fetch_assoc()): 
                $stockClass = 'stock-high';
                if ($row['stock'] == 0) $stockClass = 'stock-zero';
                elseif ($row['stock'] < 10) $stockClass = 'stock-low';
                
                // Lấy ảnh từ cột image_primary
                $image_file = !empty($row['image_primary']) ? $row['image_primary'] : '';
                $has_image = !empty($image_file) && file_exists(__DIR__ . '/images/products/' . $image_file);
                
                // Chuyển đổi mã danh mục thành tên hiển thị
                $category_display = isset($categoryNames[$row['category']]) ? $categoryNames[$row['category']] : $row['category'];
            ?>
                <tr>
                    <td>#<?php echo $row['id']; ?></td>
                    <td>
                        <?php if ($has_image): ?>
                            <img src="images/products/<?php echo $image_file; ?>" class="product-image" alt="<?php echo $row['name']; ?>">
                        <?php else: ?>
                            <span style="color: #999;">📷 No image</span>
                        <?php endif; ?>
                    </td>
                    <td style="max-width: 300px;"><?php echo htmlspecialchars(mb_substr($row['name'], 0, 60)); ?></td>
                    <td><?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($category_display); ?></td>
                    <td>
                        <span class="stock-badge <?php echo $stockClass; ?>">
                            <?php echo $row['stock']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn-edit">✏️ Sửa</a>
                            <a href="products_list.php?delete=1&id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">🗑️ Xóa</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <p>Không có sản phẩm nào.</p>
            <a href="add_product.php">Thêm sản phẩm mới</a>
        </div>
    <?php endif; ?>

    <div style="margin-top: 20px; text-align: center;">
        <a href="index.php" style="color: #0a58ca; text-decoration: none;">← Quay lại trang chủ</a>
    </div>
</div>

</body>
</html>