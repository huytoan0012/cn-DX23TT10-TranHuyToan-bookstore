<?php
include "config.php";

// Kiểm tra user id=1
$sql = "SELECT id, username, role FROM users WHERE id = 1";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

echo "<pre>";
echo "Database:\n";
print_r($user);
echo "</pre>";

// Kiểm tra session
session_start();
echo "<pre>";
echo "Session:\n";
print_r($_SESSION['user'] ?? 'Chưa đăng nhập');
echo "</pre>";
?>