<?php
session_start();
require_once 'db_connect.php';

// 1. Kiểm tra Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('location: dangnhap.php?error=noaccess');
    exit;
}

// 2. Kiểm tra có phải POST request không
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_tac_gia = $_SESSION['user_id'] ?? $_SESSION['id'];
    
    // 3. Lấy dữ liệu từ form
    $tieu_de = trim($_POST['tieu_de'] ?? '');
    $id_danh_muc = (int)($_POST['id_danh_muc'] ?? 0);
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    $trang_thai = $_POST['trang_thai'] ?? 'draft'; 

    // 4. Validate dữ liệu cơ bản
    if (empty($tieu_de) || empty($id_danh_muc) || empty($noi_dung)) {
        $_SESSION['action_message'] = 'Lỗi: Vui lòng điền đầy đủ Tiêu đề, Danh mục và Nội dung.';
        $_SESSION['action_type'] = 'error';
        header('Location: admin_vietbai.php');
        exit;
    }

    // 5. Tạo Slug (Tên đường dẫn URL)
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $tieu_de), '-'));

    // 6. Xử lý File Upload (Nếu có)
    $duong_dan_media = null;
    $loai_media = null; // <-- SỬA LỖI Ở ĐÂY: Khởi tạo là null
    
    if (isset($_FILES['featured_media']) && $_FILES['featured_media']['error'] == 0) {
        $upload_dir = 'uploads/articles/'; 
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = time() . '_' . basename($_FILES['featured_media']['name']);
        $target_path = $upload_dir . $filename;
        $file_type = $_FILES['featured_media']['type'];

        // Phân loại media
        if (strpos($file_type, 'video') !== false) {
            $loai_media = 'video';
        } elseif (strpos($file_type, 'image') !== false) {
            $loai_media = 'image';
        } else {
            $_SESSION['action_message'] = 'Lỗi: Định dạng file không được hỗ trợ.';
            $_SESSION['action_type'] = 'error';
            header('Location: admin_vietbai.php');
            exit;
        }

        // Di chuyển file
        if (move_uploaded_file($_FILES['featured_media']['tmp_name'], $target_path)) {
            $duong_dan_media = $target_path;
        } else {
            $_SESSION['action_message'] = 'Lỗi khi tải file lên.';
            $_SESSION['action_type'] = 'error';
            header('Location: admin_vietbai.php');
            exit;
        }
    }

    // 7. Xử lý ngày xuất bản
    $ngay_xuat_ban = null;
    if ($trang_thai == 'published') {
        $ngay_xuat_ban = date('Y-m-d H:i:s'); 
    }

    // 8. Chèn vào CSDL bảng bai_viet
    try {
        $sql = "INSERT INTO bai_viet 
                    (id_danh_muc, id_tac_gia, tieu_de, slug, noi_dung, 
                     duong_dan_media_dai_dien, loai_media, trang_thai, ngay_xuat_ban, ngay_tao) 
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        // Bây giờ, nếu không upload file, $duong_dan_media và $loai_media đều là null
        $stmt->execute([
            $id_danh_muc,
            $id_tac_gia,
            $tieu_de,
            $slug,
            $noi_dung,
            $duong_dan_media, // Sẽ là null nếu không upload
            $loai_media,      // Sẽ là null nếu không upload
            $trang_thai,
            $ngay_xuat_ban
        ]);

        // 9. Chuyển hướng thành công Về trang tài khoản
        $_SESSION['post_action_msg'] = 'Đã lưu bài viết thành công!';
        $_SESSION['post_action_type'] = 'success';
        header('Location: taikhoan.php');
        exit;

    } catch (Exception $e) {
        // Xử lý lỗi slug bị trùng (hoặc lỗi CSDL khác)
        // Lưu ý: Cần kiểm tra mã lỗi $e->getCode() nếu muốn thông báo lỗi "slug bị trùng"
        $_SESSION['action_message'] = 'Lỗi CSDL: ' . $e->getMessage();
        $_SESSION['action_type'] = 'error';
        header('Location: admin_vietbai.php');
        exit;
    }

} else {
    // Không phải POST request, đẩy về trang chủ
    header('Location: trangchu.php');
    exit;
}
?>