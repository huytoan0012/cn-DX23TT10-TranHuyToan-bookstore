<?php include "config.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sản Phẩm</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .products-container {
            max-width: 1000px;
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
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .btn-edit:hover {
            background: #0a4fa1;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .no-products {
            text-align: center;
            padding: 40px;
            color: #999;
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
            border-left: 4px solid #f5c6cb;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>
<body>

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

    <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Tìm kiếm sản phẩm..." class="search">
    </div>

    <div class="header-actions">
        <a href="login.php" class="action-link login-link">
            <span class="action-icon">👤</span>
            Đăng nhập
        </a>
        <a href="cart.php" class="action-link cart-link">
            <span class="action-icon">🛒</span>
            Giỏ hàng (0)
        </a>
    </div>
</div>

<div class="products-container">
    <h2>
        📦 Quản Lý Sản Phẩm
        <a href="add_product.php" class="add-btn">➕ Thêm Sản Phẩm</a>
    </h2>

    <?php
    $message = '';
    $messageType = '';

    // Xử lý xóa sản phẩm
    if (isset($_GET['delete']) && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "DELETE FROM products WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            $message = '✅ Xóa sản phẩm thành công!';
            $messageType = 'success';
        } else {
            $message = '❌ Lỗi: ' . $conn->error;
            $messageType = 'error';
        }
    }

    if (isset($_GET['added'])) {
        $message = '✅ Thêm sản phẩm thành công!';
        $messageType = 'success';
    }
    ?>

    <?php if ($message): ?>
        <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

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
    $sql = "SELECT id, name, price, category, image, stock FROM products ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
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
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td>
                            <?php if ($row['image']): ?>
                                <img src="images/products/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="product-image">
                            <?php else: ?>
                                <span style="color: #999;">Không có ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo $row['stock']; ?></td>
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
        <?php
    } else {
        ?>
        <div class="no-products">
            <p>Không có sản phẩm nào. <a href="add_product.php">Thêm sản phẩm mới</a></p>
        </div>
        <?php
    }
    ?>

    <div style="margin-top: 20px; text-align: center;">
        <a href="index.php" style="color: #0a58ca; text-decoration: none;">← Quay lại trang chủ</a>
    </div>
</div>

</body>
</html>
