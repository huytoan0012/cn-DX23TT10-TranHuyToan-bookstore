<?php include "config.php"; ?>

<?php
// Hàm upload ảnh
function uploadImage($file) {
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        return false;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    $new_name = time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = 'images/products/' . $new_name;
    
    if (!is_dir('images/products')) {
        mkdir('images/products', 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_name;
    }
    
    return false;
}

$message = '';
$messageType = '';
$product = null;

// Kiểm tra ID sản phẩm
if (!isset($_GET['id'])) {
    $message = '❌ Lỗi: Không tìm thấy sản phẩm!';
    $messageType = 'error';
} else {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $message = '❌ Lỗi: Sản phẩm không tồn tại!';
        $messageType = 'error';
    }
}

// Xử lý xóa ảnh phụ (qua AJAX hoặc form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_single_image']) && isset($_POST['image_name'])) {
    $image_name = $_POST['image_name'];
    $current_images = explode(',', $product['images']);
    $new_images = array();
    
    foreach ($current_images as $img) {
        if (trim($img) !== $image_name) {
            $new_images[] = trim($img);
        }
    }
    
    // Xóa file ảnh
    if (file_exists('images/products/' . $image_name)) {
        unlink('images/products/' . $image_name);
    }
    
    $new_images_string = implode(',', $new_images);
    $conn->query("UPDATE products SET images = '$new_images_string' WHERE id = $id");
    
    // Cập nhật lại product
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();
    
    $message = '✅ Đã xóa ảnh!';
    $messageType = 'success';
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product']) && $product) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $publisher = isset($_POST['publisher']) ? trim($_POST['publisher']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $image_primary = $product['image_primary'];
    
    // Lấy danh sách ảnh phụ hiện tại
    $existing_images = !empty($product['images']) ? explode(',', $product['images']) : array();
    
    // Xóa ảnh phụ được chọn
    if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $img_to_delete) {
            $key = array_search($img_to_delete, $existing_images);
            if ($key !== false) {
                if (file_exists('images/products/' . $img_to_delete)) {
                    unlink('images/products/' . $img_to_delete);
                }
                unset($existing_images[$key]);
            }
        }
    }
    
    // Upload ảnh chính mới
    if (isset($_FILES['image_primary']) && $_FILES['image_primary']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['image_primary']);
        if ($uploaded) {
            // Xóa ảnh chính cũ
            if ($image_primary && file_exists('images/products/' . $image_primary)) {
                unlink('images/products/' . $image_primary);
            }
            $image_primary = $uploaded;
        }
    }
    
    // Upload thêm ảnh phụ mới
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = array(
                    'name' => $_FILES['images']['name'][$key],
                    'tmp_name' => $tmp_name,
                    'size' => $_FILES['images']['size'][$key],
                    'error' => $_FILES['images']['error'][$key]
                );
                $uploaded = uploadImage($file);
                if ($uploaded) {
                    $existing_images[] = $uploaded;
                }
            }
        }
    }
    
    // Kiểm tra thông tin bắt buộc
    if (empty($name) || empty($price) || empty($category)) {
        $message = '⚠️ Vui lòng điền đầy đủ thông tin bắt buộc!';
        $messageType = 'error';
    } elseif (!is_numeric($price) || $price <= 0) {
        $message = '⚠️ Giá sản phẩm phải là số dương!';
        $messageType = 'error';
    } else {
        $images_string = implode(',', $existing_images);
        
        $name_escaped = $conn->real_escape_string($name);
        $price_escaped = $conn->real_escape_string($price);
        $category_escaped = $conn->real_escape_string($category);
        $author_escaped = $conn->real_escape_string($author);
        $publisher_escaped = $conn->real_escape_string($publisher);
        $description_escaped = $conn->real_escape_string($description);
        $image_primary_escaped = $conn->real_escape_string($image_primary);
        $images_string_escaped = $conn->real_escape_string($images_string);
        
        $sql = "UPDATE products SET 
                name='$name_escaped', 
                price='$price_escaped', 
                category='$category_escaped', 
                author='$author_escaped', 
                publisher='$publisher_escaped', 
                description='$description_escaped', 
                image_primary='$image_primary_escaped', 
                images='$images_string_escaped', 
                stock='$stock' 
                WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            $message = '✅ Cập nhật sản phẩm thành công!';
            $messageType = 'success';
            // Cập nhật lại dữ liệu
            $result = $conn->query("SELECT * FROM products WHERE id = $id");
            $product = $result->fetch_assoc();
        } else {
            $message = '❌ Lỗi: ' . $conn->error;
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chỉnh Sửa Sản Phẩm</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-product-container {
            max-width: 700px;
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
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
            border-left: 4px solid #dc3545;
        }
        
        h2 {
            margin-top: 0;
            color: #333;
            text-align: center;
        }
        
        .current-image {
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 6px;
            text-align: center;
        }
        
        .current-image img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
        }
        
        .images-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .image-item {
            position: relative;
            display: inline-block;
        }
        
        .image-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .delete-image-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .delete-image-btn:hover {
            background: #c82333;
        }
        
        .image-checkbox {
            margin-right: 8px;
        }
        
        small {
            color: #666;
            font-size: 12px;
        }
        
        .required {
            color: red;
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

<div class="edit-product-container">
    <h2>✏️ CHỈNH SỬA SẢN PHẨM</h2>

    <?php if ($message): ?>
        <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($product): ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên Sản Phẩm <span class="required">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Giá (VND) <span class="required">*</span></label>
                <input type="number" name="price" value="<?php echo $product['price']; ?>" min="0" step="1000" required>
            </div>

            <div class="form-group">
                <label>Nhà Xuất Bản</label>
                <input type="text" name="publisher" value="<?php echo htmlspecialchars($product['publisher'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>📦 Số Lượng Tồn Kho</label>
                <input type="number" name="stock" value="<?php echo $product['stock'] ?? 0; ?>" min="0">
                <small>Nhập số lượng sản phẩm hiện có trong kho</small>
            </div>

            <div class="form-group">
                <label>Tác Giả</label>
                <input type="text" name="author" value="<?php echo htmlspecialchars($product['author'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Danh Mục <span class="required">*</span></label>
                <select name="category" required>
                    <option value="sach_vietnam" <?php echo $product['category'] === 'sach_vietnam' ? 'selected' : ''; ?>>Sách Khảo Cứu & Di Sản</option>
                    <option value="sach_nuoc_ngoai" <?php echo $product['category'] === 'sach_nuoc_ngoai' ? 'selected' : ''; ?>>Nghệ Thuật & Kiến Trúc Việt</option>
                    <option value="van_phong_pham" <?php echo $product['category'] === 'van_phong_pham' ? 'selected' : ''; ?>>Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ</option>
                    <option value="do_choi" <?php echo $product['category'] === 'do_choi' ? 'selected' : ''; ?>>Văn Hóa Ẩm Thực & Phong Vị Bản Địa</option>
                    <option value="qua_tang" <?php echo $product['category'] === 'qua_tang' ? 'selected' : ''; ?>>Ấn Phẩm Văn Hóa</option>
                </select>
            </div>

            <div class="form-group">
                <label>📸 Ảnh bìa chính</label>
                <input type="file" name="image_primary" accept="image/*">
                <?php if ($product['image_primary']): ?>
                    <div class="current-image">
                        <img src="images/products/<?php echo $product['image_primary']; ?>" alt="Ảnh chính">
                        <small>Ảnh bìa chính hiện tại</small>
                    </div>
                <?php endif; ?>
                <small>Chọn ảnh mới để thay thế ảnh chính</small>
            </div>

            <div class="form-group">
                <label>🖼️ Ảnh phụ (thêm mới)</label>
                <input type="file" name="images[]" accept="image/*" multiple>
                <small>Giữ Ctrl để chọn nhiều ảnh (bìa sau, mục lục, chi tiết...)</small>
            </div>

            <?php if (!empty($product['images'])): 
                $images_list = explode(',', $product['images']);
            ?>
            <div class="form-group">
                <label>📷 Ảnh phụ hiện tại (tích chọn để xóa)</label>
                <div class="images-gallery">
                    <?php foreach($images_list as $img): 
                        $img = trim($img);
                        if(empty($img)) continue;
                    ?>
                        <label class="image-item">
                            <input type="checkbox" name="delete_images[]" value="<?php echo $img; ?>" class="image-checkbox">
                            <img src="images/products/<?php echo $img; ?>">
                        </label>
                    <?php endforeach; ?>
                </div>
                <small>☑ Tích vào ảnh muốn xóa, sau đó click "Cập nhật"</small>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Mô Tả Chi Tiết</label>
                <textarea name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" name="update_product" class="btn-submit">💾 CẬP NHẬT</button>
                <a href="products_list.php" class="btn-cancel">← QUAY LẠI</a>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>