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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $author = isset($_POST['author']) ? trim($_POST['author']) : '';
    $publisher = isset($_POST['publisher']) ? trim($_POST['publisher']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $stock = intval($_POST['stock'] ?? 0);
    
    $image_primary = '';
    $images_array = array();

    // Kiểm tra thông tin bắt buộc
    if (empty($name) || empty($price) || empty($category)) {
        $message = '⚠️ Vui lòng điền đầy đủ thông tin bắt buộc (Tên, Giá, Danh mục)!';
        $messageType = 'error';
    } elseif (!is_numeric($price) || $price <= 0) {
        $message = '⚠️ Giá sản phẩm phải là số dương!';
        $messageType = 'error';
    } else {
        // Upload ảnh chính
        if (isset($_FILES['image_primary']) && $_FILES['image_primary']['error'] === UPLOAD_ERR_OK) {
            $uploaded = uploadImage($_FILES['image_primary']);
            if ($uploaded) {
                $image_primary = $uploaded;
            }
        }
        
        // Upload nhiều ảnh phụ
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
                        $images_array[] = $uploaded;
                    }
                }
            }
        }
        
        $images_string = implode(',', $images_array);
        
        // Escape dữ liệu để tránh SQL injection
        $name_escaped = $conn->real_escape_string($name);
        $price_escaped = $conn->real_escape_string($price);
        $category_escaped = $conn->real_escape_string($category);
        $author_escaped = $conn->real_escape_string($author);
        $publisher_escaped = $conn->real_escape_string($publisher);
        $description_escaped = $conn->real_escape_string($description);
        $image_primary_escaped = $conn->real_escape_string($image_primary);
        $images_string_escaped = $conn->real_escape_string($images_string);
        
        $sql = "INSERT INTO products (name, price, category, author, publisher, description, image_primary, images, stock) 
                VALUES ('$name_escaped', '$price_escaped', '$category_escaped', '$author_escaped', '$publisher_escaped', '$description_escaped', '$image_primary_escaped', '$images_string_escaped', '$stock_escaped')";
        
        if ($conn->query($sql) === TRUE) {
            header('Location: products_list.php?added=1');
            exit;
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
    <title>Thêm Sản Phẩm - Nhà Sách Á Đông</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* CSS bổ sung riêng cho form thêm sản phẩm */
        .add-product-wrapper {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .add-product-card {
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
        
        .btn-reset {
            background: #6c757d;
            color: white;
        }
        
        .btn-reset:hover {
            background: #5a6268;
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
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="add-product-wrapper">
    <div class="add-product-card">
        <div class="card-header">
            <h2>
                <span>➕</span> 
                Thêm Sản Phẩm Mới
            </h2>
        </div>
        
        <div class="card-body">
            <?php if ($message): ?>
                <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên Sản Phẩm <span class="required">*</span></label>
                    <input type="text" name="name" placeholder="Ví dụ: Sách lịch sử Việt Nam" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Giá (VND) <span class="required">*</span></label>
                        <input type="number" name="price" placeholder="Ví dụ: 100000" min="0" step="1000" required>
                    </div>

                    <div class="form-group">
                        <label>📦 Số Lượng Tồn Kho</label>
                        <input type="number" name="stock" value="0" min="0">
                        <small>Số lượng sản phẩm hiện có trong kho</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nhà Xuất Bản</label>
                        <input type="text" name="publisher" placeholder="Ví dụ: NXB Kim Đồng">
                    </div>

                    <div class="form-group">
                        <label>Tác Giả</label>
                        <input type="text" name="author" placeholder="Ví dụ: Nguyễn Nhật Ánh">
                    </div>
                </div>

                <div class="form-group">
                    <label>Danh Mục <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">-- Chọn danh mục --</option>
                        <option value="sach_vietnam">Sách Khảo Cứu & Di Sản</option>
                        <option value="sach_nuoc_ngoai">Nghệ Thuật & Kiến Trúc Việt</option>
                        <option value="van_phong_pham">Văn Học & Tinh Hoa Nghệ Thuật Ngôn Từ</option>
                        <option value="do_choi">Văn Hóa Ẩm Thực & Phong Vị Bản Địa</option>
                        <option value="qua_tang">Ấn Phẩm Văn Hóa</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>📸 Ảnh bìa chính</label>
                        <input type="file" name="image_primary" accept="image/*">
                        <small>Ảnh hiển thị chính trên trang sản phẩm (JPG, PNG, GIF - tối đa 5MB)</small>
                    </div>

                    <div class="form-group">
                        <label>🖼️ Ảnh phụ (có thể chọn nhiều)</label>
                        <input type="file" name="images[]" accept="image/*" multiple>
                        <small>Giữ Ctrl (hoặc Cmd) để chọn nhiều ảnh</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô Tả Chi Tiết</label>
                    <textarea name="description" placeholder="Nhập mô tả chi tiết về sản phẩm..."></textarea>
                    <small>Tối đa 1000 ký tự</small>
                </div>

                <hr>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">💾 THÊM SẢN PHẨM</button>
                    <button type="reset" class="btn-reset">🔄 XÓA FORM</button>
                    <a href="products_list.php" class="btn-back">← QUAY LẠI</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>