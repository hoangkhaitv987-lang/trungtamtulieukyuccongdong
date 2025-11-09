<?php
session_start();
require_once 'db_connect.php'; 

// 1. Kiểm tra Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('location: dangnhap.php?error=noaccess');
    exit;
}

$admin_id = $_SESSION['user_id'] ?? $_SESSION['id'];

// 2. Kiểm tra có phải POST request không và đủ dữ liệu không
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['noi_dung_phan_hoi'])) {
    
    $request_id = (int)$_POST['request_id'];
    $reply_content = trim($_POST['noi_dung_phan_hoi']);

    if (empty($reply_content)) {
        $_SESSION['action_message'] = 'Lỗi: Nội dung phản hồi không được để trống.';
        $_SESSION['action_type'] = 'error';
        header('Location: admin_hotro.php');
        exit;
    }

    try {
        // 3. Cập nhật yêu cầu hỗ trợ trong CSDL
        $sql = "UPDATE yeu_cau_ho_tro 
                SET 
                    noi_dung_phan_hoi = ?, 
                    id_nguoi_phan_hoi = ?, 
                    ngay_phan_hoi = NOW(), 
                    trang_thai = 'da_phan_hoi'
                WHERE 
                    id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reply_content, $admin_id, $request_id]);

        $_SESSION['action_message'] = 'Đã gửi phản hồi thành công!';
        $_SESSION['action_type'] = 'success';

    } catch (Exception $e) {
        $_SESSION['action_message'] = 'Lỗi CSDL: ' . $e->getMessage();
        $_SESSION['action_type'] = 'error';
    }

} else {
    $_SESSION['action_message'] = 'Yêu cầu không hợp lệ.';
    $_SESSION['action_type'] = 'error';
}

header('Location: admin_hotro.php');
exit;
?>