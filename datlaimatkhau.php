<?php
session_start();

require_once __DIR__ . '/db_connect.php';
function render_form($token, $error = '') {
    $safe_token = htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $error_html = $error ? "<p style='color:red;'>" . htmlspecialchars($error) . "</p>" : '';
    echo <<<HTML
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Đặt lại mật khẩu</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px}form{max-width:420px}input{display:block;margin:8px 0;padding:8px;width:100%}</style>
</head>
<body>
  <h2>Đặt lại mật khẩu</h2>
  $error_html
  <form method="post" action="">
    <input type="hidden" name="token" value="$safe_token">
    <label>Mật khẩu mới</label>
    <input type="password" name="password" required minlength="8">
    <label>Xác nhận mật khẩu</label>
    <input type="password" name="password_confirm" required minlength="8">
    <button type="submit">Cập nhật mật khẩu</button>
  </form>
</body>
</html>
HTML;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if (empty($token) || !preg_match('/^[0-9a-f]{64}$/', $token)) {
        header('Location: quenmatkhau.php?error=invalid_token');
        exit;
    }

    if (empty($password) || $password !== $password_confirm) {
        render_form($token, 'Mật khẩu trống hoặc hai mật khẩu không khớp.');
        exit;
    }

    if (strlen($password) < 8) {
        render_form($token, 'Mật khẩu phải có ít nhất 8 ký tự.');
        exit;
    }
    $token_hash = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id FROM nguoi_dung WHERE ma_dat_lai = ? AND het_han_dat_lai > NOW() LIMIT 1');
    $stmt->execute([$token_hash]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: quenmatkhau.php?error=invalid_or_expired');
        exit;
    }
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt_up = $pdo->prepare('UPDATE nguoi_dung SET mat_khau_bam = ?, ma_dat_lai = NULL, het_han_dat_lai = NULL WHERE id = ?');
    $stmt_up->execute([$new_hash, $user['id']]);
    header('Location: dangnhap.php?success=password_reset');
    exit;

}
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if (empty($token) || !preg_match('/^[0-9a-f]{64}$/', $token)) {
    header('Location: quenmatkhau.php?error=invalid_token');
    exit;
}
$token_hash = hash('sha256', $token);
$stmt = $pdo->prepare('SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW() LIMIT 1');
$stmt->execute([$token_hash]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: quenmatkhau.php?error=invalid_or_expired');
    exit;
}
render_form($token);

?>
