<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    }

    if (isset($_POST['remove'])) {
        $id = intval($_POST['remove']);
        unset($_SESSION['cart'][$id]);
        $_SESSION['flash_message'] = 'Sản phẩm đã được xóa khỏi giỏ hàng.';
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $_SESSION['flash_message'] = 'Giỏ hàng đã được xóa.';
    }

    if (isset($_POST['checkout'])) {
        // Lấy giỏ hàng từ session
        $cartItems = $_SESSION['cart'] ?? [];
        
        if (!empty($cartItems)) {
            $conn->begin_transaction();
            $error = false;
            $errorMessage = '';
            
            try {
                foreach ($cartItems as $productId => $quantity) {
                    $productId = intval($productId);
                    $quantity = intval($quantity);
                    
                    // ✅ SỬA LỖI: Lấy thông tin sản phẩm từ database thay vì mảng $products
                    $stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
                    $stmt->bind_param("i", $productId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    $stmt->close();
                    
                    if (!$product) {
                        throw new Exception("Sản phẩm ID $productId không tồn tại!");
                    }
                    
                    if ($product['stock'] < $quantity) {
                        throw new Exception("Không đủ hàng cho sản phẩm ID $productId. Tồn kho: {$product['stock']}");
                    }
                    
                    $totalPrice = $product['price'] * $quantity;
                    
                    // Ghi vào bảng sales
                    $sql = "INSERT INTO sales (product_id, quantity, total_price) VALUES (?, ?, ?)";
                    $stmt2 = $conn->prepare($sql);
                    $stmt2->bind_param("iid", $productId, $quantity, $totalPrice);
                    $stmt2->execute();
                    $stmt2->close();
                    
                    // Cập nhật tồn kho
                    $newStock = $product['stock'] - $quantity;
                    $updateStmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                    $updateStmt->bind_param("ii", $newStock, $productId);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                $conn->commit();
                $_SESSION['cart'] = []; // Xóa giỏ hàng sau khi thanh toán thành công
                $_SESSION['flash_message'] = '✅ Thanh toán thành công! Cảm ơn bạn đã mua hàng.';
                
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['flash_message'] = '❌ Lỗi: ' . $e->getMessage();
            }
        } else {
            $_SESSION['flash_message'] = '⚠️ Giỏ hàng trống, không thể thanh toán.';
        }
        
        header('Location: cart.php');
        exit;
    }

    header('Location: cart.php');
    exit;
}

// Lấy giỏ hàng từ session
$cartItems = $_SESSION['cart'] ?? [];
$products = [];
$total = 0;

if (!empty($cartItems)) {
    $ids = array_map('intval', array_keys($cartItems));
    $idList = implode(',', $ids);
    $sql = "SELECT * FROM products WHERE id IN ($idList)";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
}

$flashMessage = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
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

        .cart-table th,
        .cart-table td {
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

        .btn-secondary,
        .btn-primary {
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

        .btn-secondary:hover {
            background: #e0e0e0;
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

        .warning-message {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }

        .product-image {
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

    <?php if ($flashMessage): 
        $msgClass = 'success-message';
        if (strpos($flashMessage, '❌') !== false) $msgClass = 'error-message';
        if (strpos($flashMessage, '⚠️') !== false) $msgClass = 'warning-message';
    ?>
        <div class="<?= $msgClass ?>"><?= htmlspecialchars($flashMessage) ?></div>
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
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $productId => $quantity):
                        if (!isset($products[$productId])) {
                            continue;
                        }
                        $product = $products[$productId];
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                        
                        $imagePath = !empty($product['image']) && file_exists(__DIR__ . '/images/products/' . $product['image'])
                            ? 'images/products/' . htmlspecialchars($product['image'])
                            : 'images/banner.jpg';
                    ?>
                        <tr>
                            <td style="display: flex; align-items: center; gap: 12px;">
                                <img src="<?= $imagePath ?>" class="product-image" alt="<?= htmlspecialchars($product['name']) ?>">
                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                            </td>
                            <td><?= number_format($product['price'], 0, ',', '.') ?> VND</td>
                            <td>
                                <input type="number" name="quantity[<?= $productId ?>]" value="<?= $quantity ?>" min="0" max="<?= $product['stock'] ?>">
                            </td>
                            <td><?= number_format($subtotal, 0, ',', '.') ?> VND</td>
                            <td>
                                <button type="submit" name="remove" value="<?= $productId ?>" class="btn-secondary">🗑️ Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div>
                    <button type="submit" name="update_cart" class="btn-primary">🔄 Cập nhật giỏ hàng</button>
                    <button type="submit" name="checkout" class="btn-primary" style="background: #28a745; margin-left: 10px;">✅ Thanh toán</button>
                </div>
                <div class="summary-total">💰 Tổng: <?= number_format($total, 0, ',', '.') ?> VND</div>
            </div>
        </form>

        <form method="post" action="cart.php" style="margin-top: 16px;">
            <button type="submit" name="clear_cart" class="btn-secondary" onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')">🗑️ Xóa toàn bộ giỏ hàng</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>