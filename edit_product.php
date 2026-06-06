<?php include "config.php"; ?>

<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_single_image']) && isset($_POST['image_name'])) {
    $image_name = $_POST['image_name'];
    $current_images = explode(',', $product['images']);
    $new_images = array();
    
    foreach ($current_images as $img) {
        if (trim($img) !== $image_name) {
            $new_images[] = trim($img);
        }
    }
    
    if (file_exists('images/products/' . $image_name)) {
        unlink('images/products/' . $image_name);
    }
    
    $new_images_string = implode(',', $new_images);
    $conn->query("UPDATE products SET images = '$new_images_string' WHERE id = $id");
    
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();
    
    $message = '✅ Đã xóa ảnh!';
    $messageType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product']) && $product) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $publisher = isset($_POST['publisher']) ? trim($_POST['publisher']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $image_primary = $product['image_primary'];
        $existing_images = !empty($product['images']) ? explode(',', $product['images']) : array();
    
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
    <title>Chỉnh Sửa Sản Phẩm - Nhà Sách Á Đông</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-product-wrapper {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .edit-product-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #0a58ca, #084298);
            padding: 20px 25px;
            color: white;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .required {
            color: #dc3545;
        }
        
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #0a58ca;
            box-shadow: 0 0 0 3px rgba(10, 88, 202, 0.1);
        }
        
        input[type="file"] {
            padding: 10px 0;
            width: 100%;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        button {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit {
            background: #28a745;
            color: white;
        }
        
        .btn-submit:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-back {
            background: #0a58ca;
            color: white;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }
        
        .btn-back:hover {
            background: #084298;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .current-image {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }
        
        .current-image img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
            cursor: pointer;
        }
        
        .image-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
            transition: all 0.3s;
        }
        
        .image-item:hover img {
            border-color: #0a58ca;
        }
        
        .image-checkbox {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 22px;
            height: 22px;
            cursor: pointer;
            background: white;
            border-radius: 50%;
        }
        
        .image-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 11px;
            margin-top: 5px;
            color: #666;
        }
        
        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .card-body {
                padding: 20px;
            }
            .form-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="edit-product-wrapper">
    <div class="edit-product-card">
        <div class="card-header">
            <h2>
                <span>✏️</span> 
                Chỉnh Sửa Sản Phẩm
            </h2>
        </div>
        
        <div class="card-body">
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

                <div class="form-row">
                    <div class="form-group">
                        <label>Giá (VND) <span class="required">*</span></label>
                        <input type="number" name="price" value="<?php echo $product['price']; ?>" min="0" step="1000" required>
                    </div>

                    <div class="form-group">
                        <label>📦 Số Lượng Tồn Kho</label>
                        <input type="number" name="stock" value="<?php echo $product['stock'] ?? 0; ?>" min="0">
                        <small>Nhập số lượng sản phẩm hiện có trong kho</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nhà Xuất Bản</label>
                        <input type="text" name="publisher" value="<?php echo htmlspecialchars($product['publisher'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Tác Giả</label>
                        <input type="text" name="author" value="<?php echo htmlspecialchars($product['author'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-group">
    <label for="category">Danh Mục <span style="color: red;">*</span></label>
    <select id="category" name="category" required>
        <option value="sach_khao_cuu_va_di_san" <?php echo ($product['category'] == 'sach_khao_cuu_va_di_san') ? 'selected' : ''; ?>>Sách Khảo Cứu & Di Sản</option>
        <option value="nghe_thuat_va_kien_truc_viet" <?php echo ($product['category'] == 'nghe_thuat_va_kien_truc_viet') ? 'selected' : ''; ?>>Nghệ Thuật & Kiến Trúc Việt</option>
        <option value="van_hoc_va_tinh_hoa_nghe_thuat_ngon_tu" <?php echo ($product['category'] == 'van_hoc_va_tinh_hoa_nghe_thuat_ngon_tu') ? 'selected' : ''; ?>>Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ</option>
        <option value="van_hoa_am_thuc_va_phong_vi" <?php echo ($product['category'] == 'van_hoa_am_thuc_va_phong_vi') ? 'selected' : ''; ?>>Văn Hóa Ẩm Thực & Phong Vị Bản Địa</option>
        <option value="an_pham_van_hoa" <?php echo ($product['category'] == 'an_pham_van_hoa') ? 'selected' : ''; ?>>Ấn Phẩm Văn Hóa</option>
    </select>
</div>

                <div class="form-row">
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
                                <span class="image-label">Chọn để xóa</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small>☑ Tích vào ảnh muốn xóa, sau đó click "CẬP NHẬT"</small>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Mô Tả Chi Tiết</label>
                    <textarea name="description" rows="5"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>

                <hr>

                <div class="form-buttons">
                    <button type="submit" name="update_product" class="btn-submit">💾 CẬP NHẬT</button>
                    <a href="products_list.php" class="btn-back">← QUAY LẠI DANH SÁCH</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>