<?php
session_start();
require_once 'db_connect.php'; 

// 1. Kiểm tra Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('location: dangnhap.php?error=noaccess');
    exit;
}

// 2. Kiểm tra có phải POST request không
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id']) && isset($_POST['action'])) {
    
    $post_id = (int)$_POST['post_id'];
    $action = $_POST['action'];

    try {
        if ($action == 'approve') {
            // 3a. DUYỆT BÀI: Cập nhật trạng thái
            $sql = "UPDATE bai_dang SET trang_thai = 'approved' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$post_id]);
            
            $_SESSION['action_message'] = 'Đã duyệt bài viết thành công!';
            $_SESSION['action_type'] = 'success';

        } elseif ($action == 'reject') {
            // 3b. TỪ CHỐI (XÓA):
            
            // Bước 1: Lấy đường dẫn file media để xóa
            $sql_find = "SELECT duong_dan_media FROM bai_dang WHERE id = ?";
            $stmt_find = $pdo->prepare($sql_find);
            $stmt_find->execute([$post_id]);
            $media_path = $stmt_find->fetchColumn();

            // Bước 2: Xóa file vật lý 
            if ($media_path && file_exists($media_path)) {
                unlink($media_path);
            }
            
            // Bước 3: Xóa bài viết khỏi CSDL 
            $sql_delete = "DELETE FROM bai_dang WHERE id = ?";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([$post_id]);
            
            $_SESSION['action_message'] = 'Đã từ chối (xóa) bài viết.';
            $_SESSION['action_type'] = 'success';
        }

    } catch (Exception $e) {
        $_SESSION['action_message'] = 'Lỗi CSDL: ' . $e->getMessage();
        $_SESSION['action_type'] = 'error';
    }

} else {
    $_SESSION['action_message'] = 'Yêu cầu không hợp lệ.';
    $_SESSION['action_type'] = 'error';
}

// Quay lại trang duyệt bài
header('Location: admin_duyetbai.php');
exit;
?>