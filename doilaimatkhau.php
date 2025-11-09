<?php
session_start();
require_once 'db_connect.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
$is_token_valid = false;
$error_message = '';

if (!empty($token)) {
    $stmt = $pdo->prepare("SELECT id FROM nguoi_dung WHERE ma_dat_lai = ? AND het_han_dat_lai > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $is_token_valid = true;
    } else {
        $error_message = "Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.";
    }
} else {
    $error_message = "Không tìm thấy mã token.";
}
if(isset($_GET['error']) && $_GET['error'] == 'mismatch') {
    $error_message = "Mật khẩu xác nhận không khớp. Vui lòng thử lại.";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
        <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="dangnhap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        </header>

    <main>
        <div class="ochuathongti content-wrapper">
            <div class="login-ochuathongti">
                <h3><i class="fas fa-lock"></i> Đặt lại mật khẩu mới</h3>

                <?php if ($is_token_valid): ?>
                    <form id="reset-form" action="xuly_datlaimatkhau.php" method="post">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label for="password">Mật khẩu mới:</label>
                            <input type="password" id="password" name="password" placeholder="Nhập mật khẩu mới" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                        </div>

                        <?php if (!empty($error_message)): ?>
                            <div class="form-error" style="display:block; color: #b50202;">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <button type="submit" class="login-button">Lưu mật khẩu</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="form-error" style="display:block; color: #b50202; text-align: center;">
                        <?php echo $error_message; ?>
                        <br><br>
                        <a href="quenmatkhau.php">Yêu cầu link mới</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer>
        </footer>
</body>
</html>