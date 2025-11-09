<?php
session_start();
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Lấy dữ liệu từ form
    $ho_ten = trim($_POST['ho-ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $chu_de = trim($_POST['chu-de'] ?? '');
    $noi_dung = trim($_POST['noi-dung'] ?? '');

    // 2. Lấy ID người dùng (nếu họ đã đăng nhập)
    $id_nguoi_gui = NULL; 
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $id_nguoi_gui = $_SESSION['user_id'] ?? $_SESSION['id'] ?? NULL;
    }

    // 3. Kiểm tra dữ liệu
    if (empty($ho_ten) || empty($email) || empty($chu_de) || empty($noi_dung)) {
        $_SESSION['support_message'] = 'Lỗi: Vui lòng điền đầy đủ tất cả các trường.';
        $_SESSION['support_message_type'] = 'error';
        header('Location: hotro.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['support_message'] = 'Lỗi: Địa chỉ email không hợp lệ.';
        $_SESSION['support_message_type'] = 'error';
        header('Location: hotro.php');
        exit;
    }

    // 4. Chèn vào CSDL
    try {
        $sql = "INSERT INTO yeu_cau_ho_tro (ho_ten, email, id_nguoi_gui, chu_de, noi_dung, trang_thai) 
                VALUES (?, ?, ?, ?, ?, 'moi')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ho_ten, $email, $id_nguoi_gui, $chu_de, $noi_dung]);

        // 5. Set thông báo thành công
        $_SESSION['support_message'] = 'Gửi yêu cầu thành công! Chúng tôi sẽ phản hồi bạn sớm nhất có thể.';
        $_SESSION['support_message_type'] = 'success';
        header('Location: hotro.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['support_message'] = 'Đã có lỗi máy chủ xảy ra. Vui lòng thử lại sau.';
        $_SESSION['support_message_type'] = 'error';
        header('Location: hotro.php');
        exit;
    }

} else {
    header('Location: trangchu.php');
    exit;
}
?>