<?php
session_start();

$conn = new mysqli("localhost", "root", "", "bookstore_db");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$userTableSql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if (!$conn->query($userTableSql)) {
    die("Lỗi tạo bảng users: " . $conn->error);
}

$productsTableSql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(100),
    author VARCHAR(255),
    publisher VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if (!$conn->query($productsTableSql)) {
    die("Lỗi tạo bảng products: " . $conn->error);
}

$salesTableSql = "CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if (!$conn->query($salesTableSql)) {
    die("Lỗi tạo bảng sales: " . $conn->error);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function cart_count() {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }

    $count = 0;
    foreach ($_SESSION['cart'] as $quantity) {
        $count += max(0, intval($quantity));
    }

    return $count;
}

function get_cart_items() {
    return $_SESSION['cart'] ?? [];
}
?>