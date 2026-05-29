<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý cập nhật giỏ hàng
    if (isset($_POST['update_cart']) && isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $productId => $quantity) {
            $id = intval($productId);
            $qty = max(0, intval($quantity));
            if ($qty > 0) {
                $_SESSION['cart'][$id] = $qty;
            } else {
                unset($_SESSION['cart'][$id]);
            }
        }
        $_SESSION['flash_message'] = 'Giỏ hàng đã được cập nhật.';
        header('Location: cart.php');
        exit;
    }

    // Xóa sản phẩm khỏi giỏ
    if (isset($_POST['remove'])) {
        $id = intval($_POST['remove']);
        unset($_SESSION['cart'][$id]);
        $_SESSION['flash_message'] = 'Sản phẩm đã được xóa.';
        header('Location: cart.php');
        exit;
    }

    // Xóa toàn bộ giỏ hàng
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $_SESSION['flash_message'] = 'Đã xóa toàn bộ giỏ hàng.';
        header('Location: cart.php');
        exit;
    }

    // Xử lý thanh toán từ popup
    if (isset($_POST['checkout_complete'])) {
        $cartItems = $_SESSION['cart'] ?? [];
        
        if (empty($cartItems)) {
            $_SESSION['flash_message'] = 'Giỏ hàng trống!';
            header('Location: cart.php');
            exit;
        }
        
        // Lấy thông tin từ form
        $customer_name = trim($_POST['customer_name']);
        $customer_email = trim($_POST['customer_email']);
        $customer_phone = trim($_POST['customer_phone']);
        $customer_address = trim($_POST['customer_address']);
        $payment_method = $_POST['payment_method'] ?? 'cod';
        
        // Validate
        $errors = [];
        if (empty($customer_name)) $errors[] = 'Vui lòng nhập họ tên';
        if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
        if (empty($customer_phone)) $errors[] = 'Vui lòng nhập số điện thoại';
        if (empty($customer_address)) $errors[] = 'Vui lòng nhập địa chỉ giao hàng';
        
        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_data'] = $_POST;
            header('Location: cart.php');
            exit;
        }
        
        // Lấy thông tin sản phẩm từ database
        $ids = array_keys($cartItems);
        $idList = implode(',', array_map('intval', $ids));
        $sql = "SELECT * FROM products WHERE id IN ($idList)";
        $result = $conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[$row['id']] = $row;
        }
        
        // Tính tổng tiền
        $total = 0;
        foreach ($cartItems as $productId => $quantity) {
            if (isset($products[$productId])) {
                $total += $products[$productId]['price'] * $quantity;
            }
        }
        
        // Tạo mã đơn hàng duy nhất
        $order_code = 'DH' . date('Ymd') . '_' . strtoupper(uniqid());
        
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        try {
            // Lưu vào bảng orders
            $stmt = $conn->prepare("INSERT INTO orders (order_code, customer_name, customer_email, customer_phone, customer_address, total_amount, payment_method) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $order_code, $customer_name, $customer_email, $customer_phone, $customer_address, $total, $payment_method);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();
            
            // Lưu chi tiết đơn hàng và cập nhật tồn kho
            foreach ($cartItems as $productId => $quantity) {
                $product = $products[$productId];
                $subtotal = $product['price'] * $quantity;
                
                // Lưu order_items
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisid", $order_id, $productId, $product['name'], $quantity, $product['price']);
                $stmt->execute();
                $stmt->close();
                
                $sales_sql = "INSERT INTO sales (product_id, quantity, total_price, sale_date) 
                  VALUES ($productId, $quantity, $subtotal, NOW())";
    $conn->query($sales_sql);
    
                // Cập nhật tồn kho
                $newStock = $product['stock'] - $quantity;
                $conn->query("UPDATE products SET stock = $newStock WHERE id = $productId");
            }
            
            $conn->commit();
            
            // Xóa giỏ hàng
            $_SESSION['cart'] = [];
            $_SESSION['flash_message'] = '✅ Đặt hàng thành công! Mã đơn hàng: ' . $order_code;
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = '❌ Lỗi: ' . $e->getMessage();
        }
        
        header('Location: cart.php');
        exit;
    }
}

// Lấy giỏ hàng hiện tại
$cartItems = $_SESSION['cart'] ?? [];
$products = [];
$total = 0;

if (!empty($cartItems)) {
    $ids = array_keys($cartItems);
    $idList = implode(',', array_map('intval', $ids));
    $sql = "SELECT * FROM products WHERE id IN ($idList)";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}

$flashMessage = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

$checkoutErrors = $_SESSION['checkout_errors'] ?? [];
$checkoutData = $_SESSION['checkout_data'] ?? [];
unset($_SESSION['checkout_errors']);
unset($_SESSION['checkout_data']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Nhà Sách Á Đông</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-page {
            max-width: 1100px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 18px rgba(0,0,0,0.08);
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .cart-table th, .cart-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #eaeaea;
            text-align: left;
        }
        .cart-table th {
            background: #f8f9ff;
        }
        .cart-table input[type="number"] {
            width: 80px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccd0da;
        }
        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .summary-total {
            font-size: 24px;
            font-weight: 700;
            color: #d93025;
        }
        .btn-secondary, .btn-primary {
            padding: 12px 18px;
            border-radius: 10px;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary {
            background: #0a58ca;
            color: white;
        }
        .btn-primary:hover {
            background: #0a4fa1;
        }
        .btn-secondary {
            background: #f4f6f9;
            color: #333;
        }
        .cart-empty {
            text-align: center;
            padding: 40px;
            color: #555;
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
        
        /* POPUP MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            max-width: 500px;
            width: 90%;
            border-radius: 16px;
            padding: 30px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .btn-checkout-submit {
            background: #28a745;
            color: white;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-checkout-submit:hover {
            background: #218838;
        }
        .error-text {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="cart-page">
    <h2>🛒 Giỏ hàng của bạn</h2>

    <?php if ($flashMessage): ?>
        <div class="success-message"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <?php if (empty($cartItems) || empty($products)): ?>
        <div class="cart-empty">
            <p>🛍️ Giỏ hàng của bạn đang trống.</p>
            <a href="index.php" class="btn-primary" style="display: inline-block; margin-top: 15px;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form method="post" action="cart.php">
            <table class="cart-table">
                <thead>
                    <tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Thành tiền</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $productId => $quantity):
                        if (!isset($products[$productId])) continue;
                        $product = $products[$productId];
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                        $imagePath = !empty($product['image_primary']) && file_exists(__DIR__ . '/images/products/' . $product['image_primary'])
                            ? 'images/products/' . htmlspecialchars($product['image_primary'])
                            : 'images/banner.jpg';
                    ?>
                        <tr>
                            <td style="display: flex; align-items: center; gap: 12px;">
                                <img src="<?= $imagePath ?>" class="product-img" alt="<?= htmlspecialchars($product['name']) ?>">
                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                            </td>
                            <td><?= number_format($product['price'], 0, ',', '.') ?>đ</td>
                            <td><input type="number" name="quantity[<?= $productId ?>]" value="<?= $quantity ?>" min="0" max="<?= $product['stock'] ?>"></td>
                            <td><?= number_format($subtotal, 0, ',', '.') ?>đ</td>
                            <td><button type="submit" name="remove" value="<?= $productId ?>" class="btn-secondary">🗑️ Xóa</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-summary">
                <div>
                    <button type="submit" name="update_cart" class="btn-primary">🔄 Cập nhật giỏ hàng</button>
                    <button type="button" id="showCheckoutModal" class="btn-primary" style="background: #28a745;">✅ Thanh toán</button>
                </div>
                <div class="summary-total">💰 Tổng: <?= number_format($total, 0, ',', '.') ?>đ</div>
            </div>
        </form>
        <form method="post" action="cart.php" style="margin-top: 16px;">
            <button type="submit" name="clear_cart" class="btn-secondary">🗑️ Xóa toàn bộ giỏ hàng</button>
        </form>
    <?php endif; ?>
</div>

<!-- POPUP THANH TOÁN -->
<div id="checkoutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📋 Thông tin đặt hàng</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form method="post" action="cart.php" id="checkoutForm">
            <input type="hidden" name="checkout_complete" value="1">
            
            <div class="form-group">
                <label>Họ và tên *</label>
                <input type="text" name="customer_name" id="customer_name" value="<?= htmlspecialchars($checkoutData['customer_name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="customer_email" id="customer_email" value="<?= htmlspecialchars($checkoutData['customer_email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Số điện thoại *</label>
                <input type="tel" name="customer_phone" id="customer_phone" value="<?= htmlspecialchars($checkoutData['customer_phone'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Địa chỉ giao hàng *</label>
                <textarea name="customer_address" id="customer_address" required><?= htmlspecialchars($checkoutData['customer_address'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Phương thức thanh toán</label>
                <select name="payment_method">
                    <option value="cod">💰 Thanh toán khi nhận hàng (COD)</option>
                    <option value="banking">🏦 Chuyển khoản ngân hàng</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Thông tin đơn hàng</label>
                <div style="background: #f5f5f5; padding: 12px; border-radius: 8px; font-size: 14px;">
                    <?php foreach ($cartItems as $productId => $quantity):
                        if (!isset($products[$productId])) continue;
                        $product = $products[$productId];
                    ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span><?= htmlspecialchars(mb_substr($product['name'], 0, 30)) ?> x <?= $quantity ?></span>
                        <span><?= number_format($product['price'] * $quantity, 0, ',', '.') ?>đ</span>
                    </div>
                    <?php endforeach; ?>
                    <hr style="margin: 10px 0;">
                    <div style="display: flex; justify-content: space-between; font-weight: bold;">
                        <span>Tổng cộng:</span>
                        <span style="color: #d93025;"><?= number_format($total, 0, ',', '.') ?>đ</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-checkout-submit">✅ Xác nhận đặt hàng</button>
        </form>
    </div>
</div>

<script>
// Hiển thị popup khi bấm nút Thanh toán
document.getElementById('showCheckoutModal')?.addEventListener('click', function() {
    document.getElementById('checkoutModal').classList.add('show');
});

// Đóng popup
function closeModal() {
    document.getElementById('checkoutModal').classList.remove('show');
}

// Đóng khi click ra ngoài
document.getElementById('checkoutModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Hiển thị popup nếu có lỗi validation
<?php if (!empty($checkoutErrors)): ?>
document.getElementById('checkoutModal').classList.add('show');
<?php endif; ?>
</script>

</body>
</html>