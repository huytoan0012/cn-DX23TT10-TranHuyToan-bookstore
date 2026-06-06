<?php include "config.php"; 

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    header('Location: login.php?redirect=orders.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$is_admin = is_admin();

// Xử lý cập nhật trạng thái đơn hàng (chỉ admin)
if ($is_admin && isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET order_status = '$status' WHERE id = $order_id");
    header('Location: orders.php');
    exit;
}

// Lấy danh sách đơn hàng
if ($is_admin) {
    // Admin xem tất cả đơn hàng
    $sql = "SELECT * FROM orders ORDER BY order_date DESC";
    $result = $conn->query($sql);
} else {
    // Khách hàng xem đơn hàng của mình (dựa trên email hoặc tên)
    // Lưu ý: Bảng orders không có cột user_id, dùng customer_email để lọc
    $email = $conn->real_escape_string($_SESSION['user']['username']);
    $sql = "SELECT * FROM orders WHERE customer_email = '$email' ORDER BY order_date DESC";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng - Nhà Sách Á Đông</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .orders-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 0;
            color: #333;
            border-left: 5px solid #0a58ca;
            padding-left: 15px;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background: #ffc107; color: #333; }
        .status-confirmed { background: #17a2b8; color: white; }
        .status-shipping { background: #0a58ca; color: white; }
        .status-completed { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .btn-detail {
            background: #0a58ca;
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            border: none;
            cursor: pointer;
        }
        .btn-detail:hover {
            background: #084298;
        }
        select {
            padding: 5px 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 12px;
            cursor: pointer;
        }
        .order-detail {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }
        .order-detail h4 {
            margin: 0 0 10px 0;
            color: #0a58ca;
        }
        .detail-row {
            display: none;
        }
        .detail-row.show {
            display: table-row;
        }
        .detail-row td {
            padding: 0;
        }
        .address-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .empty-orders {
            text-align: center;
            padding: 50px;
            color: #999;
        }
        @media (max-width: 1000px) {
            .orders-container {
                overflow-x: auto;
            }
            table {
                min-width: 1000px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="orders-container">
    <h2>📋 QUẢN LÝ ĐƠN HÀNG</h2>
    
    <?php if ($result && $result->num_rows > 0): ?>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ giao hàng</th>
                    <th>Tổng tiền</th>
                    <th>Phương thức</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = $result->fetch_assoc()): 
                    $statusClass = 'status-pending';
                    $statusText = 'Chờ xác nhận';
                    
                    if ($order['order_status'] == 'confirmed') {
                        $statusClass = 'status-confirmed';
                        $statusText = 'Đã xác nhận';
                    } elseif ($order['order_status'] == 'shipping') {
                        $statusClass = 'status-shipping';
                        $statusText = 'Đang giao';
                    } elseif ($order['order_status'] == 'completed') {
                        $statusClass = 'status-completed';
                        $statusText = 'Hoàn thành';
                    } elseif ($order['order_status'] == 'cancelled') {
                        $statusClass = 'status-cancelled';
                        $statusText = 'Đã hủy';
                    }
                    
                    $phone = $order['customer_phone'] ?? 'Chưa có';
                    $address = $order['customer_address'] ?? 'Chưa có địa chỉ';
                    $addressDisplay = mb_strlen($address) > 40 ? mb_substr($address, 0, 40) . '...' : $address;
                ?>
                <tr>
                    <td><strong>#<?= $order['id'] ?></strong><br><small><?= $order['order_code'] ?? '' ?></small></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= htmlspecialchars($phone) ?></td>
                    <td class="address-cell" title="<?= htmlspecialchars($address) ?>"><?= htmlspecialchars($addressDisplay) ?></td>
                    <td><strong><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</strong></td>
                    <td><?= $order['payment_method'] == 'cod' ? 'COD' : 'Chuyển khoản' ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= $statusText ?></span></td>
                    <td style="white-space: nowrap;">
                        <button class="btn-detail" onclick="toggleDetail(<?= $order['id'] ?>)">📋 Chi tiết</button>
                        <?php if ($is_admin): ?>
                        <form method="post" style="display: inline-block; margin-left: 5px;">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Chờ xác nhận</option>
                                <option value="confirmed" <?= $order['order_status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                <option value="shipping" <?= $order['order_status'] == 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                <option value="completed" <?= $order['order_status'] == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr id="detail-<?= $order['id'] ?>" class="detail-row">
                    <td colspan="9">
                        <div class="order-detail">
                            <h4>📦 Sản phẩm đã đặt:</h4>
                            <?php
                            $detail_sql = "SELECT * FROM order_items WHERE order_id = " . $order['id'];
                            $detail_result = $conn->query($detail_sql);
                            if ($detail_result && $detail_result->num_rows > 0):
                            ?>
                            <table style="width: 100%; background: white; border-collapse: collapse; margin-bottom: 15px;">
                                <thead>
                                    <tr style="background: #e9ecef;">
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($item = $detail_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= number_format($item['price'], 0, ',', '.') ?>đ</td>
                                        <td><?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?>đ</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p>Không có chi tiết đơn hàng</p>
                            <?php endif; ?>
                            
                            <?php if ($order['customer_address'] && $order['customer_address'] != ''): ?>
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                <strong>📍 Địa chỉ giao hàng:</strong> <?= htmlspecialchars($order['customer_address']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-orders">
        <p>📭 Bạn chưa có đơn hàng nào.</p>
        <a href="index.php" style="display: inline-block; margin-top: 15px; background: #0a58ca; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">🛍️ Mua sắm ngay</a>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleDetail(orderId) {
    const detailRow = document.getElementById('detail-' + orderId);
    detailRow.classList.toggle('show');
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>