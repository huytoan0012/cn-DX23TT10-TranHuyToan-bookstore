<?php
include "config.php";

$username = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || $password === '' || $confirmPassword === '') {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.';
            $stmt->close();
        } else {
            $stmt->close();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, "user")');
            $stmt->bind_param('ss', $username, $passwordHash);
            if ($stmt->execute()) {
                $_SESSION['user'] = [
                    'id' => $stmt->insert_id,
                    'username' => $username,
                ];
                header('Location: index.php');
                exit;
            }
            $error = 'Không thể tạo tài khoản. Vui lòng thử lại.';
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="auth-form-container">
    <h2>Đăng ký</h2>
    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
        <label for="username">Tên đăng nhập</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>

        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Xác nhận mật khẩu</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" class="btn-primary">Đăng ký</button>
    </form>
    <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</div>

</body>
</html>
