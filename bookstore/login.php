<?php
include "config.php";

$username = '';
$error = '';
$redirect = 'index.php';
if (!empty($_GET['redirect']) && basename($_GET['redirect']) === $_GET['redirect']) {
    $redirect = $_GET['redirect'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập tên đăng nhập và mật khẩu.';
    } else {
        $stmt = $conn->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
            ];
            header('Location: ' . $redirect);
            exit;
        }

        $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="auth-form-container">
    <h2>Đăng nhập</h2>
    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="username">Tên đăng nhập</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>

        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn-primary">Đăng nhập</button>
    </form>
    <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
</div>

</body>
</html>
