<?php
session_start();
require_once 'db_connect.php';

// Mặc định trả về lỗi
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $response['message'] = 'Bạn cần đăng nhập để thích bài viết.';
    echo json_encode($response);
    exit;
}
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'];

// 2. Kiểm tra có post_id không
if (isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];

    try {
        // 3. Kiểm tra xem user đã thích bài này chưa
        $sql_check = "SELECT * FROM luot_thich_bai_dang WHERE id_nguoi_dung = ? AND id_bai_dang = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$user_id, $post_id]);
        $existing_like = $stmt_check->fetch();

        if ($existing_like) {
            // 4a. ĐÃ THÍCH -> BỎ THÍCH (DELETE)
            $sql_delete = "DELETE FROM luot_thich_bai_dang WHERE id_nguoi_dung = ? AND id_bai_dang = ?";
            $pdo->prepare($sql_delete)->execute([$user_id, $post_id]);
            $response['liked'] = false; 
        } else {
            // 4b. CHƯA THÍCH -> THÍCH (INSERT)
            $sql_insert = "INSERT INTO luot_thich_bai_dang (id_nguoi_dung, id_bai_dang) VALUES (?, ?)";
            $pdo->prepare($sql_insert)->execute([$user_id, $post_id]);
            $response['liked'] = true; 
        }

        // 5. Lấy tổng số like mới
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM luot_thich_bai_dang WHERE id_bai_dang = ?");
        $stmt_count->execute([$post_id]);
        $new_count = $stmt_count->fetchColumn();

        $response['success'] = true;
        $response['new_count'] = $new_count;
        $response['message'] = 'Cập nhật thành công.';

    } catch (Exception $e) {
        $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
    }
}

// 6. Trả về kết quả JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>