<?php
session_start();
require_once 'db_connect.php'; 

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: dangnhap.php');
    exit;
}
// 2. Kiểm tra có phải là POST request không
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'] ?? $_SESSION['id'];

    // 3. Lấy dữ liệu từ form
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $location = $_POST['location']; 
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 4. Validate dữ liệu
    
    // 4.1. Kiểm tra email mới có bị trùng với user khác không
    try {
        $sql_check_email = "SELECT id FROM nguoi_dung WHERE email = ? AND id != ?";
        $stmt_check = $pdo->prepare($sql_check_email);
        $stmt_check->execute([$email, $user_id]);
        if ($stmt_check->fetch()) {
            $_SESSION['update_message'] = 'Lỗi: Email này đã tồn tại, vui lòng chọn email khác.';
            $_SESSION['update_type'] = 'error';
            header('Location: capnhattaikhoan.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['update_message'] = 'Lỗi CSDL: ' . $e->getMessage();
        $_SESSION['update_type'] = 'error';
        header('Location: capnhattaikhoan.php');
        exit;
    }

    // 4.2. Kiểm tra mật khẩu
    $new_password_hash = null;
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $_SESSION['update_message'] = 'Lỗi: Mật khẩu mới không khớp.';
            $_SESSION['update_type'] = 'error';
            header('Location: capnhattaikhoan.php');
            exit;
        }
        $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
    }

    // 4.3. Xử lý upload Avatar (nếu có)
    $new_avatar_path = null;
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] == 0) {
        
        // Kiểm tra dung lượng (20MB)
        if ($_FILES['avatar_file']['size'] > 20 * 1024 * 1024) { 
            $_SESSION['update_message'] = 'Lỗi: File ảnh quá lớn. Chỉ cho phép dưới 20MB.';
            $_SESSION['update_type'] = 'error';
            header('Location: capnhattaikhoan.php');
            exit;
        }

        // Kiểm tra loại file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['avatar_file']['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['update_message'] = 'Lỗi: Chỉ chấp nhận file ảnh JPG, PNG, GIF.';
            $_SESSION['update_type'] = 'error';
            header('Location: capnhattaikhoan.php');
            exit;
        }

        // Tạo đường dẫn
        $upload_dir = 'uploads/avatars/'; 
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = $user_id . '_' . time() . '_' . basename($_FILES['avatar_file']['name']);
        $target_path = $upload_dir . $filename;

        // Di chuyển file
        if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $target_path)) {
            $new_avatar_path = $target_path;

            // (Tùy chọn) Xóa avatar cũ
            try {
                $stmt_old_avatar = $pdo->prepare("SELECT duong_dan_avatar FROM nguoi_dung WHERE id = ?");
                $stmt_old_avatar->execute([$user_id]);
                $old_avatar = $stmt_old_avatar->fetchColumn();
                
                if ($old_avatar && file_exists($old_avatar) && $old_avatar != 'image/avatar_default.png') {
                    unlink($old_avatar);
                }
            } catch (Exception $e) {
               
            }

        } else {
            $_SESSION['update_message'] = 'Lỗi: Không thể tải ảnh lên.';
            $_SESSION['update_type'] = 'error';
            header('Location: capnhattaikhoan.php');
            exit;
        }
    }

    // 5. Xây dựng câu lệnh UPDATE động
    try {
        $sql = "UPDATE nguoi_dung SET ho_ten = ?, email = ?, gioi_tinh = ?, ngay_sinh = ?, tinh_thanh = ?";
        $params = [$fullname, $email, $gender, $dob, $location];

        if ($new_password_hash) {
            $sql .= ", mat_khau_bam = ?";
            $params[] = $new_password_hash;
        }
        
        if ($new_avatar_path) {
            $sql .= ", duong_dan_avatar = ?";
            $params[] = $new_avatar_path;
        }

        $sql .= " WHERE id = ?";
        $params[] = $user_id;

        // 6. chạy
        $stmt_update = $pdo->prepare($sql);
        $stmt_update->execute($params);

        // 7. Cập nhật lại session
        $_SESSION['fullname'] = $fullname;
        if ($new_avatar_path) {
            $_SESSION['avatar'] = $new_avatar_path;
        }

        // 8. Đặt thông báo thành công và chuyển hướng VỀ TRANG TÀI KHOẢN
        $_SESSION['post_action_msg'] = 'Cập nhật thông tin tài khoản thành công!';
        $_SESSION['post_action_type'] = 'success';
        header('Location: taikhoan.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['update_message'] = 'Lỗi CSDL khi cập nhật: ' . $e->getMessage();
        $_SESSION['update_type'] = 'error';
        header('Location: capnhattaikhoan.php');
        exit;
    }

} else {
    header('Location: trangchu.php');
    exit;
}
?>