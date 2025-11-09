<?php
session_start();

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Nếu chưa đăng nhập, không cho làm gì cả
    header('location: dangnhap.php');
    exit;
}

// 2. KIỂM TRA POST REQUEST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'])) {
    
    // 3. KẾT NỐI CSDL 
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "congthongtina80";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Lỗi kết nối CSDL: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

    // Lấy ID
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['id']; 

    try {
        // 4. LẤY THÔNG TIN BÀI VIẾT 
        $sql_find = "SELECT id_nguoi_dung, duong_dan_media FROM bai_dang WHERE id = ?";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->bind_param("i", $post_id);
        $stmt_find->execute();
        $result = $stmt_find->get_result();

        if ($result->num_rows == 1) {
            $post = $result->fetch_assoc();

            // 5. KIỂM TRA QUYỀN SỞ HỮU 
            if ($post['user_id'] == $user_id || (isset($_SESSION['role']) && $_SESSION['role'] == 'admin')) {
                
                // 6. XÓA FILE VẬT LÝ
                if (!empty($post['media_path']) && file_exists($post['media_path'])) {
                    unlink($post['media_path']); 
                }

                // 7. XÓA BÀI VIẾT KHỎI CSDL
                $sql_delete = "DELETE FROM bai_dang WHERE id = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("i", $post_id);
                $stmt_delete->execute();

                if ($stmt_delete->affected_rows > 0) {
                    $_SESSION['post_action_msg'] = 'Đã xóa bài viết thành công!';
                    $_SESSION['post_action_type'] = 'success';
                } else {
                    $_SESSION['post_action_msg'] = 'Lỗi: Không thể xóa bài viết khỏi CSDL.';
                    $_SESSION['post_action_type'] = 'error';
                }
                $stmt_delete->close();
            
            } else {
                // Lỗi: Không có quyền
                $_SESSION['post_action_msg'] = 'Bạn không có quyền xóa bài viết này.';
                $_SESSION['post_action_type'] = 'error';
            }
        } else {
            // Lỗi: Không tìm thấy bài
            $_SESSION['post_action_msg'] = 'Không tìm thấy bài viết để xóa.';
            $_SESSION['post_action_type'] = 'error';
        }
        $stmt_find->close();

    } catch (Exception $e) {
        $_SESSION['post_action_msg'] = 'Đã xảy ra lỗi: ' . $e->getMessage();
        $_SESSION['post_action_type'] = 'error';
    }

    $conn->close();

} else {
    // Lỗi: Yêu cầu không hợp lệ
    $_SESSION['post_action_msg'] = 'Yêu cầu không hợp lệ.';
    $_SESSION['post_action_type'] = 'error';
}

header('location: taikhoan.php');
exit;
?>