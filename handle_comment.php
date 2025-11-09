<?php
session_start();
require_once 'db_connect.php';
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $response['message'] = 'Bạn cần đăng nhập để bình luận.';
    echo json_encode($response);
    exit;
}
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'];
$fullname = $_SESSION['fullname'] ?? 'Người dùng'; 

// 2. Kiểm tra dữ liệu POST
if (isset($_POST['post_id']) && isset($_POST['content'])) {
    $post_id = (int)$_POST['post_id'];
    $content = trim($_POST['content']);

    if (empty($content)) {
        $response['message'] = 'Vui lòng nhập nội dung bình luận.';
        echo json_encode($response);
        exit;
    }

    try {
        // 3. Chèn bình luận vào CSDL
        $sql_insert = "INSERT INTO binh_luan (id_bai_dang, id_nguoi_dung, noi_dung, ngay_tao) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql_insert);
        $stmt->execute([$post_id, $user_id, $content]);
        
        $new_comment_id = $pdo->lastInsertId();

        // 4. Chuẩn bị dữ liệu trả về cho JavaScript
        $response['success'] = true;
        $response['message'] = 'Bình luận thành công.';
        $response['comment'] = [
            'id' => $new_comment_id,
            'fullname' => htmlspecialchars($fullname),
            'content' => htmlspecialchars($content), 
            'ngay_tao' => date('d/m/Y H:i') 
        ];

    } catch (Exception $e) {
        $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
    }
}

// 5. Trả về kết quả JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>