<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dangnhap.php');
    exit;
}

require_once __DIR__ . '/db_connect.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password_input = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password_input)) {
    header('Location: dangnhap.php?error=empty_fields');
    exit;
}

try {
    $sql = 'SELECT id, ho_ten AS fullname, mat_khau_bam AS password_hash, vai_tro AS role, duong_dan_avatar AS avatar_path FROM nguoi_dung WHERE email = ? LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password_input, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar'] = $user['avatar_path'] ?? null;

        header('Location: trangchu.php');
        exit;
    } else {
        header('Location: dangnhap.php?error=wrong_credentials');
        exit;
    }

} catch (Exception $e) {
    header('Location: dangnhap.php?error=db_connect');
    exit;
}

?>