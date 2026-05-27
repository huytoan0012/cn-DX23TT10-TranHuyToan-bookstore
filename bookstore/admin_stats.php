<?php
include "config.php";

// Kiểm tra đăng nhập (chỉ admin mới xem được)
// Chỉ admin mới được xem thống kê
if (!is_logged_in()) {
    header('Location: login.php?redirect=admin_stats.php');
    exit;
}

if (!is_admin()) {
    header('Location: index.php');
    exit;
}

// Xử lý lọc theo thời gian
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'month';
$custom_start = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$custom_end = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$dateCondition = "";
$chartTitle = "Doanh thu tháng này";

switch($filter) {
    case 'today':
        $dateCondition = "DATE(sale_date) = CURDATE()";
        $chartTitle = "Doanh thu hôm nay";
        break;
    case 'week':
        $dateCondition = "YEARWEEK(sale_date) = YEARWEEK(CURDATE())";
        $chartTitle = "Doanh thu tuần này";
        break;
    case 'month':
        $dateCondition = "MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())";
        $chartTitle = "Doanh thu tháng này";
        break;
    case 'year':
        $dateCondition = "YEAR(sale_date) = YEAR(CURDATE())";
        $chartTitle = "Doanh thu năm nay";
        break;
    case 'custom':
        if ($custom_start && $custom_end) {
            $dateCondition = "DATE(sale_date) BETWEEN '$custom_start' AND '$custom_end'";
            $chartTitle = "Doanh thu từ $custom_start đến $custom_end";
        } else {
            $dateCondition = "MONTH(sale_date) = MONTH(CURDATE())";
        }
        break;
}

// Lấy tổng doanh thu
$sqlTotal = "SELECT SUM(total_price) as total, COUNT(*) as orders FROM sales WHERE $dateCondition";
$totalResult = $conn->query($sqlTotal);
$totalData = $totalResult->fetch_assoc();
$totalRevenue = $totalData['total'] ?? 0;
$totalOrders = $totalData['orders'] ?? 0;

// Lấy doanh thu theo ngày cho biểu đồ (7 ngày gần nhất hoặc theo filter)
if ($filter == 'week' || $filter == 'today') {
    $sqlChart = "SELECT DATE(sale_date) as date, SUM(total_price) as daily_total, COUNT(*) as daily_orders 
                 FROM sales 
                 WHERE $dateCondition 
                 GROUP BY DATE(sale_date) 
                 ORDER BY date ASC";
} elseif ($filter == 'month') {
    $sqlChart = "SELECT DAY(sale_date) as day, SUM(total_price) as daily_total, COUNT(*) as daily_orders 
                 FROM sales 
                 WHERE $dateCondition 
                 GROUP BY DAY(sale_date) 
                 ORDER BY day ASC";
} elseif ($filter == 'year') {
    $sqlChart = "SELECT MONTH(sale_date) as month, SUM(total_price) as monthly_total, COUNT(*) as monthly_orders 
                 FROM sales 
                 WHERE $dateCondition 
                 GROUP BY MONTH(sale_date) 
                 ORDER BY month ASC";
} else {
    $sqlChart = "SELECT DATE(sale_date) as date, SUM(total_price) as daily_total, COUNT(*) as daily_orders 
                 FROM sales 
                 WHERE $dateCondition 
                 GROUP BY DATE(sale_date) 
                 ORDER BY date ASC";
}

$chartResult = $conn->query($sqlChart);
$labels = [];
$values = [];
$orderCounts = [];

while($row = $chartResult->fetch_assoc()) {
    if ($filter == 'month') {
        $labels[] = "Ngày " . $row['day'];
    } elseif ($filter == 'year') {
        $labels[] = "Tháng " . $row['month'];
    } else {
        $labels[] = $row['date'];
    }
    $values[] = $row['daily_total'];
    $orderCounts[] = $row['daily_orders'] ?? $row['monthly_orders'] ?? 0;
}

// Lấy top 5 sản phẩm bán chạy
$sqlTopProducts = "SELECT p.name, SUM(s.quantity) as total_sold, SUM(s.total_price) as revenue
                   FROM sales s
                   JOIN products p ON s.product_id = p.id
                   WHERE $dateCondition
                   GROUP BY s.product_id
                   ORDER BY total_sold DESC
                   LIMIT 5";
$topProducts = $conn->query($sqlTopProducts);

// Lấy thống kê tổng quan thêm
$sqlAvgOrder = "SELECT AVG(total_price) as avg_order FROM sales WHERE $dateCondition";
$avgResult = $conn->query($sqlAvgOrder);
$avgOrderValue = $avgResult->fetch_assoc()['avg_order'] ?? 0;

// Lấy sản phẩm bán chạy nhất
$sqlBestSeller = "SELECT p.name, SUM(s.quantity) as total_sold
                  FROM sales s
                  JOIN products p ON s.product_id = p.id
                  WHERE $dateCondition
                  GROUP BY s.product_id
                  ORDER BY total_sold DESC
                  LIMIT 1";
$bestSeller = $conn->query($sqlBestSeller)->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê doanh thu - Nhà Sách Á Đông</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .stats-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 15px;
        }
        
        .filter-btn {
            padding: 8px 20px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: #0a58ca;
            color: white;
            border-color: #0a58ca;
        }
        
        .filter-btn:hover {
            background: #0a58ca;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #0a58ca;
        }
        
        .stat-card .sub {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .chart-container h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        canvas {
            max-height: 400px;
        }
        
        .top-products {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .top-products h3 {
            margin-bottom: 20px;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-name {
            font-weight: 500;
        }
        
        .product-stats {
            color: #0a58ca;
            font-weight: bold;
        }
        
        .custom-date-form {
            display: inline-flex;
            gap: 10px;
            align-items: center;
        }
        
        .custom-date-form input {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        .btn-submit {
            background: #28a745;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .best-seller-badge {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .best-seller-badge h4 {
            margin: 0;
            color: #856404;
        }
        
        .best-seller-badge .product-name {
            font-size: 24px;
            font-weight: bold;
            color: #d9534f;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="stats-container">
    <div class="stats-header">
        <h2>📊 Thống kê doanh thu</h2>
        
        <div class="filter-bar">
            <a href="?filter=today" class="filter-btn <?= $filter == 'today' ? 'active' : '' ?>">Hôm nay</a>
            <a href="?filter=week" class="filter-btn <?= $filter == 'week' ? 'active' : '' ?>">Tuần này</a>
            <a href="?filter=month" class="filter-btn <?= $filter == 'month' ? 'active' : '' ?>">Tháng này</a>
            <a href="?filter=year" class="filter-btn <?= $filter == 'year' ? 'active' : '' ?>">Năm nay</a>
            
            <form method="get" class="custom-date-form" style="display: inline-flex;">
                <input type="hidden" name="filter" value="custom">
                <input type="date" name="start_date" value="<?= $custom_start ?>">
                <span>→</span>
                <input type="date" name="end_date" value="<?= $custom_end ?>">
                <button type="submit" class="btn-submit">Lọc</button>
            </form>
        </div>
    </div>
    
    <?php if ($bestSeller && $bestSeller['total_sold'] > 0): ?>
    <div class="best-seller-badge">
        <h4>🏆 Sản phẩm bán chạy nhất</h4>
        <div class="product-name"><?= htmlspecialchars($bestSeller['name']) ?></div>
        <div>Đã bán: <?= number_format($bestSeller['total_sold']) ?> sản phẩm</div>
    </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>💰 Tổng doanh thu</h3>
            <div class="value"><?= number_format($totalRevenue, 0, ',', '.') ?>đ</div>
            <div class="sub"><?= $chartTitle ?></div>
        </div>
        
        <div class="stat-card">
            <h3>📦 Tổng đơn hàng</h3>
            <div class="value"><?= number_format($totalOrders) ?></div>
            <div class="sub">đơn hàng</div>
        </div>
        
        <div class="stat-card">
            <h3>📊 Giá trị đơn hàng trung bình</h3>
            <div class="value"><?= number_format($avgOrderValue, 0, ',', '.') ?>đ</div>
            <div class="sub">mỗi đơn hàng</div>
        </div>
    </div>
    
    <div class="chart-container">
        <h3>📈 Biểu đồ doanh thu - <?= $chartTitle ?></h3>
        <canvas id="revenueChart"></canvas>
    </div>
    
    <div class="top-products">
        <h3>🏅 Top 5 sản phẩm bán chạy nhất</h3>
        <?php if ($topProducts && $topProducts->num_rows > 0): ?>
            <?php while($product = $topProducts->fetch_assoc()): ?>
                <div class="product-item">
                    <span class="product-name"><?= htmlspecialchars($product['name']) ?></span>
                    <span class="product-stats">
                        Đã bán: <?= $product['total_sold'] ?> | 
                        Doanh thu: <?= number_format($product['revenue'], 0, ',', '.') ?>đ
                    </span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; color: #999;">Chưa có dữ liệu bán hàng</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Vẽ biểu đồ
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Doanh thu (VND)',
            data: <?= json_encode($values) ?>,
            borderColor: '#0a58ca',
            backgroundColor: 'rgba(10, 88, 202, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }, {
            label: 'Số đơn hàng',
            data: <?= json_encode($orderCounts) ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        let value = context.raw;
                        if (context.dataset.label === 'Doanh thu (VND)') {
                            return label + ': ' + new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                        }
                        return label + ': ' + value;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Doanh thu (VND)'
                },
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                    }
                }
            },
            y1: {
                position: 'right',
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Số đơn hàng'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});
</script>

</body>
</html>