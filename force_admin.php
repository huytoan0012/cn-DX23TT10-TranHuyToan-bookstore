<?php
session_start();

// Đảm bảo session có role = admin
$_SESSION['user'] = [
    'id' => 1,
    'username' => '1',
    'role' => 'admin'
];

echo "✅ Đã ép session thành ADMIN!";
echo '<br><a href="index.php">👉 Vào trang chủ ngay</a>';
?>