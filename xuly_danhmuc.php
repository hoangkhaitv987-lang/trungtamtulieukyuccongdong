<?php
session_start();
require_once 'db_connect.php'; 

// 1. Kiểm tra Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('location: dangnhap.php?error=noaccess');
    exit;
}
// 2. Kiểm tra có phải POST request không và có action không
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    $action = $_POST['action'];

    try {
        //XỬ LÝ TẠO MỚI 
        if ($action == 'create') {
            $ten = trim($_POST['ten'] ?? '');
            $mo_ta = trim($_POST['mo_ta'] ?? '');

            if (empty($ten)) {
                $_SESSION['action_message'] = 'Lỗi: Tên danh mục không được để trống.';
                $_SESSION['action_type'] = 'error';
                header('Location: admin_danhmuc.php');
                exit;
            }

            // Tạo slug từ tên
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $ten), '-'));
            $slug = preg_replace('/-+/', '-', $slug);

            // Thêm vào CSDL
            $sql = "INSERT INTO danh_muc (ten, slug, mo_ta) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ten, $slug, $mo_ta]);

            $_SESSION['action_message'] = 'Đã tạo danh mục mới thành công!';
            $_SESSION['action_type'] = 'success';
        
        //  XỬ LÝ XÓA 
        } elseif ($action == 'delete') {
            $id = (int)($_POST['id_danh_muc'] ?? 0);

            if (empty($id)) {
                $_SESSION['action_message'] = 'Lỗi: ID danh mục không hợp lệ.';
                $_SESSION['action_type'] = 'error';
                header('Location: admin_danhmuc.php');
                exit;
            }
            
            // Cẩn trọng: Không cho xóa ID=1 
            if ($id == 1) {
                $_SESSION['action_message'] = 'Lỗi: Không thể xóa danh mục mặc định (ID 1).';
                $_SESSION['action_type'] = 'error';
                header('Location: admin_danhmuc.php');
                exit;
            }

            // Xóa khỏi CSDL
            // Do CSDL có "ON DELETE CASCADE", tất cả bài viết trong danh mục này cũng sẽ bị xóa
            $sql = "DELETE FROM danh_muc WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $_SESSION['action_message'] = 'Đã xóa danh mục (và các bài viết liên quan) thành công!';
            $_SESSION['action_type'] = 'success';

        } else {
            $_SESSION['action_message'] = 'Lỗi: Hành động không rõ ràng.';
            $_SESSION['action_type'] = 'error';
        }

    } catch (Exception $e) {
        // Xử lý lỗi 
        if ($e->getCode() == 23000) { 
             $_SESSION['action_message'] = 'Lỗi: Tên danh mục hoặc slug này đã tồn tại.';
        } else {
             $_SESSION['action_message'] = 'Lỗi CSDL: ' . $e->getMessage();
        }
        $_SESSION['action_type'] = 'error';
    }

} else {
    // Nếu không phải admin
    $_SESSION['action_message'] = 'Lỗi: Yêu cầu không hợp lệ.';
    $_SESSION['action_type'] = 'error';
}

header('Location: admin_danhmuc.php');
exit;
?>