<?php include "config.php"; ?>

<?php
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $publisher = isset($_POST['publisher']) ? trim($_POST['publisher']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image = '';

    if (empty($name) || empty($price) || empty($category)) {
        $message = '⚠️ Vui lòng điền đầy đủ thông tin bắt buộc (Tên, Giá, Danh mục)!';
        $messageType = 'error';
    } elseif (!is_numeric($price) || $price <= 0) {
        $message = '⚠️ Giá sản phẩm phải là số dương!';
        $messageType = 'error';
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($file_ext, $allowed_ext)) {
                $message = '⚠️ Chỉ cho phép upload ảnh (JPG, PNG, GIF)!';
                $messageType = 'error';
            } elseif ($file_size > 5 * 1024 * 1024) {
                $message = '⚠️ Kích thước ảnh không được vượt quá 5MB!';
                $messageType = 'error';
            } else {
                $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = 'images/products/' . $new_file_name;

                if (!is_dir('images/products')) {
                    mkdir('images/products', 0755, true);
                }

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $image = $new_file_name;
                } else {
                    $message = '❌ Lỗi: Không thể upload ảnh!';
                    $messageType = 'error';
                }
            }
        }

        if ($messageType !== 'error') {
            $name_escaped = $conn->real_escape_string($name);
            $price_escaped = $conn->real_escape_string($price);
            $category_escaped = $conn->real_escape_string($category);
            $author_escaped = $conn->real_escape_string($author);
            $publisher_escaped = $conn->real_escape_string($publisher);
            $description_escaped = $conn->real_escape_string($description);
            $image_escaped = $conn->real_escape_string($image);

            $sql = "INSERT INTO products (name, price, category, author, publisher, description, image) VALUES ('$name_escaped', '$price_escaped', '$category_escaped', '$author_escaped', '$publisher_escaped', '$description_escaped', '$image_escaped')";

            if ($conn->query($sql) === TRUE) {
                header('Location: products_list.php?added=1');
                exit;
            } else {
                $message = '❌ Lỗi: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .add-product-container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #0a58ca;
            box-shadow: 0 0 0 3px rgba(10, 88, 202, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-submit {
            background: #0a58ca;
            color: white;
        }

        .btn-submit:hover {
            background: #0a4fa1;
        }

        .btn-cancel {
            background: #f0f0f0;
            color: #333;
        }

        .btn-cancel:hover {
            background: #e0e0e0;
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

        h2 {
            margin-top: 0;
            color: #333;
            text-align: center;
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

<div class="add-product-container">
    <h2>➕ Thêm Sản Phẩm Mới</h2>

    <?php if ($message): ?>
        <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Tên Sản Phẩm <span style="color: red;">*</span></label>
            <input type="text" id="name" name="name" placeholder="Ví dụ: Sách lịch sử Việt Nam" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="price">Giá (VND) <span style="color: red;">*</span></label>
            <input type="number" id="price" name="price" placeholder="Ví dụ: 100000" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" min="0" step="1000" required>
        </div>

        <div class="form-group">
            <label for="author">Tác Giả</label>
            <input type="text" id="author" name="author" placeholder="Ví dụ: Sơn Tùng" value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="publisher">Nhà Xuất Bản</label>
            <input type="text" id="publisher" name="publisher" placeholder="Ví dụ: NXB Kim Đồng" value="<?php echo isset($_POST['publisher']) ? htmlspecialchars($_POST['publisher']) : ''; ?>">
        </div>
<div class="form-group">
    <label for="stock">Số Lượng Tồn Kho</label>
    <input type="number" id="stock" name="stock" placeholder="Ví dụ: 50" value="0" min="0">
    <small style="color: #666;">Nhập số lượng sản phẩm có trong kho</small>
</div>
        <div class="form-group">
            <label for="category">Danh Mục <span style="color: red;">*</span></label>
            <select id="category" name="category" required>
                <option value="">-- Chọn danh mục --</option>
                <option value="sach_vietnam" <?php echo (isset($_POST['category']) && $_POST['category'] === 'sach_vietnam') ? 'selected' : ''; ?>>Sách Việt Nam</option>
                <option value="sach_nuoc_ngoai" <?php echo (isset($_POST['category']) && $_POST['category'] === 'sach_nuoc_ngoai') ? 'selected' : ''; ?>>Foreign Books</option>
                <option value="van_phong_pham" <?php echo (isset($_POST['category']) && $_POST['category'] === 'van_phong_pham') ? 'selected' : ''; ?>>Văn Phòng Phẩm</option>
                <option value="do_choi" <?php echo (isset($_POST['category']) && $_POST['category'] === 'do_choi') ? 'selected' : ''; ?>>Đồ Chơi</option>
                <option value="qua_tang" <?php echo (isset($_POST['category']) && $_POST['category'] === 'qua_tang') ? 'selected' : ''; ?>>Quà Tặng</option>
            </select>
        </div>

        <div class="form-group">
            <label for="image">Ảnh Sản Phẩm</label>
            <input type="file" id="image" name="image" accept="image/*">
            <small style="color: #666;">Định dạng: JPG, PNG, GIF. Tối đa 5MB</small>
        </div>

        <div class="form-group">
            <label for="description">Mô Tả Chi Tiết</label>
            <textarea id="description" name="description" placeholder="Nhập mô tả sản phẩm..." maxlength="1000"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>

        <div class="form-buttons">
            <button type="submit" class="btn-submit">💾 Thêm Sản Phẩm</button>
            <button type="reset" class="btn-cancel">🔄 Xóa Form</button>
        </div>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <a href="index.php" style="color: #0a58ca; text-decoration: none;">← Quay lại trang chủ</a>
    </div>
</div>

</body>
</html>
