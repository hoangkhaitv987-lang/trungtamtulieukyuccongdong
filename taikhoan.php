<?php
session_start();
// --- SỬA 1: Dùng kết nối PDO ---
require_once 'db_connect.php'; 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: dangnhap.php');
    exit;
}

$message = '';
$message_type = ''; 
if (isset($_SESSION['post_action_msg'])) {
    $message = $_SESSION['post_action_msg'];
    $message_type = $_SESSION['post_action_type'];
    unset($_SESSION['post_action_msg'], $_SESSION['post_action_type']);
}

// 3. dịch tỉnh/thành phố
$location_map = [
    'hanoi' => 'TP Hà Nội',
    'hue' => 'TP Huế',
    'quangninh' => 'Quảng Ninh',
    'caobang' => 'Cao Bằng',
    'langson' => 'Lạng Sơn',
    'laichau' => 'Lai Châu',
    'dienbien' => 'Điện Biên',
    'sonla' => 'Sơn La',
    'thanhhoa' => 'Thanh Hóa',
    'nghean' => 'Nghệ An',
    'hatinh' => 'Hà Tĩnh',
    'tuyenquang' => 'Tuyên Quang (Tuyên Quang, Hà Giang)',
    'laocai' => 'Lào Cai (Lào Cai, Yên Bái)',
    'thainguyen' => 'Thái Nguyên (Thái Nguyên, Bắc Kạn)',
    'phutho' => 'Phú Thọ (Phú Thọ, Vĩnh Phúc, Hòa Bình)',
    'bacninh' => 'Bắc Ninh (Bắc Ninh, Bắc Giang)',
    'hungyen' => 'Hưng Yên (Hưng Yên, Thái Bình)',
    'haiphong' => 'TP Hải Phòng (Hải Phòng, Hải Dương)',
    'ninhbinh' => 'Ninh Bình (Ninh Bình, Hà Nam, Nam Định)',
    'quangtri' => 'Quảng Trị (Quảng Trị, Quảng Bình)',
    'danang' => 'TP Đà Nẵng (Đà Nẵng, Quảng Nam)',
    'quangngai' => 'Quảng Ngãi (Quảng Ngãi, Kon Tum)',
    'gialai' => 'Gia Lai (Gia Lai, Bình Định)',
    'khanhhoa' => 'Khánh Hòa (Khánh Hòa, Ninh Thuận)',
    'lamdong' => 'Lâm Đồng (Lâm Đồng, Đắk Nông, Bình Thuận)',
    'daklak' => 'Đắk Lắk (Đắk Lắk, Phú Yên)',
    'hcm' => 'TPHCM (TPHCM, Bà Rịa - Vũng Tàu, Bình Dương)',
    'dongnai' => 'Đồng Nai (Đồng Nai, Bình Phước)',
    'tayninh' => 'Tây Ninh (Tây Ninh, Long An)',
    'cantho' => 'TP Cần Thơ (Cần Thơ, Sóc Trăng, Hậu Giang)',
    'vinhlong' => 'Vĩnh Long (Vĩnh Long, Bến Tre, Trà Vinh)',
    'dongthap' => 'Đồng Tháp (Đồng Tháp, Tiền Giang)',
    'camau' => 'Cà Mau (Cà Mau, Bạc Liêu)',
    'angiang' => 'An Giang (An Giang, Kiên Giang)'
];

// 4. LẤY DỮ LIỆU NGƯỜI DÙNG TỪ CSDL
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (empty($user_id)) {
    header('location: dangnhap.php');
    exit;
}

// Khai báo biến
$user = [];
$user_posts = [];
$support_requests = [];
$admin_articles = [];
$is_admin = false;

try {
    // Query 1: Lấy thông tin user
    $sql_user = "SELECT ho_ten AS fullname, email, vai_tro AS role, ngay_tao AS created_at, 
                       gioi_tinh, ngay_sinh, tinh_thanh, duong_dan_avatar AS avatar_path 
                       FROM nguoi_dung WHERE id = ?";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        header('location: dangnhap.php?error=user_not_found');
        exit;
    }

    // Xử lý biến
    $fullname = htmlspecialchars($user['fullname']);
    $email = htmlspecialchars($user['email']);
    $role_display = ($user['role'] == 'admin') ? 'Quản trị viên' : 'Thành viên';
    $is_admin = ($user['role'] == 'admin'); 
    
    if (!isset($_SESSION['role'])) {
        $_SESSION['role'] = $user['role'];
    }
    
    $avatar_path = htmlspecialchars($user['avatar_path'] ?? 'image/avatar_default.png');
    if (empty($user['avatar_path'])) {
        $avatar_path = $_SESSION['avatar'] ?? 'image/avatar_default.png'; 
    }
    
    $gender_display = 'Chưa cập nhật'; 
    if ($user['gioi_tinh'] == 'male') $gender_display = 'Nam';
    elseif ($user['gioi_tinh'] == 'female') $gender_display = 'Nữ';

    $dob_display = 'Chưa cập nhật';
    if (!empty($user['ngay_sinh'])) $dob_display = date("d/m/Y", strtotime($user['ngay_sinh']));

    $location_display = 'Chưa cập nhật';
    $location_value = $user['tinh_thanh']; 
    if (!empty($location_value)) $location_display = $location_map[$location_value] ?? htmlspecialchars($location_value);
    
    $created_at_display = date("d/m/Y H:i:s", strtotime($user['created_at']));

    // Query 2: Lấy bài viết cộng đồng của user (bảng bai_dang)
    $sql_posts = "SELECT id, tieu_de, ngay_tao, duong_dan_media, loai_media 
                   FROM bai_dang WHERE id_nguoi_dung = ? ORDER BY ngay_tao DESC";
    $stmt_posts = $pdo->prepare($sql_posts);
    $stmt_posts->execute([$user_id]);
    $user_posts = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

    // Query 3: Lấy yêu cầu hỗ trợ của user
    $sql_support = "SELECT 
                        yc.chu_de, yc.noi_dung, yc.trang_thai, yc.ngay_gui,
                        yc.noi_dung_phan_hoi, yc.ngay_phan_hoi,
                        admin.ho_ten AS ten_admin_phan_hoi
                    FROM yeu_cau_ho_tro AS yc
                    LEFT JOIN nguoi_dung AS admin ON yc.id_nguoi_phan_hoi = admin.id
                    WHERE yc.id_nguoi_gui = ?
                    ORDER BY yc.ngay_gui DESC";
    $stmt_support = $pdo->prepare($sql_support);
    $stmt_support->execute([$user_id]);
    $support_requests = $stmt_support->fetchAll(PDO::FETCH_ASSOC);

    // Query 4: Lấy bài viết chính thức của Admin (bảng bai_viet)
    if ($is_admin) {
        $sql_admin_articles = "SELECT id, tieu_de, ngay_tao, trang_thai 
                               FROM bai_viet 
                               WHERE id_tac_gia = ? 
                               ORDER BY ngay_tao DESC";
        $stmt_admin_articles = $pdo->prepare($sql_admin_articles);
        $stmt_admin_articles->execute([$user_id]);
        $admin_articles = $stmt_admin_articles->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    die("Lỗi CSDL: " . $e->getMessage());
}

// (Hàm dịch trạng thái giữ nguyên)
function translate_support_status($status) {
    switch ($status) {
        case 'moi': return 'Mới';
        case 'da_xem': return 'Đã xem';
        case 'da_phan_hoi': return 'Đã phản hồi';
        default: return $status;
    }
}
function translate_article_status($status) {
    switch ($status) {
        case 'published': return 'Đã xuất bản';
        case 'draft': return 'Bản nháp';
        default: return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
    <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">    
    <style>
        /* ===HEADER/FOOTER/NAV === */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333;
            line-height: 1.6;
        }

        .ochuathongti {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        a {
            color: #333;
            text-decoration: none;
        }

        a:hover {
            color: #b50202;
            text-decoration: underline;
        }

        h1, h2, h3, h4 .chuh3 {
            margin: 0;
            padding: 0;
        }
        main h3 { color: #333; }

        ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        img {
            max-width: 100%;
            height: auto;
        }
        /* --- timkiem --- */

        .timkiem-box {
            display: flex;
            width: 100%;
        }

        .timkiem-box input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            font-size: 16px;
            border: 2px solid #b50202;
            border-right: none;
            border-radius: 5px 0 0 5px;
        }

        .timkiem-box button {
            padding: 0 20px;
            font-size: 18px;
            border: none;
            background-color: #b50202;
            color: #fff;
            cursor: pointer;
            border-radius: 05px 5px 0;
        }

        .timkiemnangcao {
            display: block;
            text-align: right;
            font-weight: bold;
            color: #ccc;
        }
        /* ---  PHANHEADER --- */
        .header-top {
            background-color: #fff;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .header-top .ochuathongti {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }

        .logo-text span {
            font-size: 16px;
            color: #b50202;
        }

        .logo-text h1 {
            font-size: 24px;
            color: #b50202;
            font-weight: bold;
        }

        .header-phai {
            display: flex;
            align-items: center;
        }

        .language-select {
            padding: 5px;
            margin-right: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .auth-links a {
            font-weight: bold;
        }

        .nav-bar {
            background-color: #b50202;
        }

        .nav-bar .ochuathongti ul {
            display: flex;
        }

        .nav-bar .ochuathongti ul li a {
            display: block;
            padding: 15px 20px;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-bar .ochuathongti ul li a:hover {
            background-color: #9a0202;
            color: #fff;
        }
        
        /* --- FOOTER --- */
        footer {
            margin-top: 20px;
        }

        .footer-top {
            background-color: #380000;
            color: #fff;
            padding: 20px 0;
        }

        .footer-top .ochuathongti {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-logo {
            width: 80px;
            height: 80px;
        }

        .footer-info {
            flex-grow: 1;
            margin: 0 20px;
        }

        .footer-info h4 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #fff; 
        }

        .footer-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .footer-info a {
            color: #fff;
            font-weight: bold;
        }

        .footer-cert img {
            height: 60px;
        }

        .footer-bottom {
            background-color: #b50202;
            color: #fff;
            padding: 15px 0;
            text-align: center;
            font-size: 13px;
        }

        .footer-bottom p {
            margin: 5px 0;
        }
        
        /* Khung nội dung trắng */
        main .content-wrapper {
            background-color: #fff;
            padding: 20px 25px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .account-container { 
            max-width: 100%; 
            margin-bottom: 30px; 
            padding: 20px;
            background-color: #fff; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
        }
        
        .account-container h3 { 
            text-align: left; 
            font-size: 20px; 
            margin-bottom: 15px; 
            color: #b50202; 
            border-bottom: 2px solid #f0f0f0; 
            padding-bottom: 10px; 
        }
        
        .account-info { font-size: 16px; line-height: 1.8; }
        .info-row { display: flex; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        .info-row strong { flex-basis: 150px; color: #333; flex-shrink: 0; }
        .info-row span { flex-grow: 1; color: #555; word-break: break-all; }
        
        /* Bảng điều khiển Admin */
        .admin-panel-link { 
            padding: 20px; 
            background-color: #fff; 
            border: 1px solid #e0baba; 
            border-radius: 8px; 
            text-align: center;
        }
        .admin-panel-link h2 { margin-top: 0; color: #9a0202; font-size: 20px; margin-bottom: 10px; }
        .admin-panel-link p { font-size: 1em; color: #333; margin-bottom: 15px; }
        
        .admin-buttons { display: flex; flex-direction: column; gap: 10px; }
        .btn-admin-duyetbai { display: block; padding: 12px 20px; background-color: #9a0202; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 1em; border-radius: 5px; transition: background-color 0.3s ease; }
        .btn-admin-duyetbai:hover { background-color: #7a0101; color: #fff; text-decoration: none; }
        .btn-admin-duyetbai i { margin-right: 8px; }
        .btn-admin-hotro { background-color: #218838; }
        .btn-admin-hotro:hover { background-color: #1e7e34; }
        .btn-admin-danhmuc { background-color: #ffc107; color: #212529; }
        .btn-admin-danhmuc:hover { background-color: #e0a800; }

        /* Danh sách bài đăng */
        .post-list { max-height: 400px; overflow-y: auto; margin-top: 20px; padding-right: 10px; }
        .post-list-item { display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .post-list-item:last-child { border-bottom: none; }
        .post-list-thumb { flex-shrink: 0; width: 80px; height: 60px; margin-right: 15px; border-radius: 5px; overflow: hidden; background-color: #f0f0f0; }
        .post-list-thumb img, .post-list-thumb video { width: 100%; height: 100%; object-fit: cover; }
        .post-list-info { flex-grow: 1; }
        .post-list-title { margin: 0; font-size: 1.1em; }
        .post-list-title a { text-decoration: none; color: #0056b3; font-weight: bold; }
        .post-list-title a:hover { text-decoration: underline; }
        .post-list-date { font-size: 0.9em; color: #666; margin-top: 5px; display: block; }
        .post-list-date i { margin-right: 5px; }
        .no-posts-message { padding: 20px; text-align: center; color: #777; font-size: 1.1em; }
        
        .add-post-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff; 
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9em;
            margin-bottom: 15px; 
            transition: background-color 0.3s ease;
        }
        .add-post-btn:hover { background-color: #0056b3; color: #fff; text-decoration: none; }
        .add-post-btn i { margin-right: 5px; }

        .post-list-actions { flex-shrink: 0; margin-left: 10px; }
        .btn-delete { padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9em; transition: background-color 0.2s ease; }
        .btn-delete:hover { background: #c82333; }
        
        /* Thông báo */
        .form-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .form-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .form-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
         
        /* Hình nền */
        body {
            background-image: url('khoanh/a80/hinhen.jpg'); 
            background-size: cover;        
            background-position: center;    
            background-attachment: fixed;   
            position: relative; 
            z-index: 1;
        }
        body::before {
            content: "";          
            position: absolute;     
            top: 0; 
            left: 0;
            width: 100%;
            height: 100%;
            background-color: black; 
            opacity: 0.8;       
            z-index: -1; 
        }

        /* Sidebar thông tin */
        .account-avatar {
            display: block;
            width: 120px;
            height: 120px;
            border-radius: 50%; 
            margin: 0 auto 20px auto; 
            object-fit: cover; 
            border: 4px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .update-action {
            text-align: center;
            margin-top: 25px; 
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        .btn-update {
            display: inline-block;
            padding: 10px 25px;
            background-color: #007bff; 
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        .btn-update:hover {
            background-color: #0056b3;
            color: #fff; 
            text-decoration: none;
        }
        .btn-update i {
            margin-right: 8px;
        }
        
        /* Danh sách Hỗ trợ */
        .support-list {
            max-height: 500px;
            overflow-y: auto;
            margin-top: 0; 
            display: flex;
            flex-direction: column;
            gap: 15px; 
            padding-right: 10px;
        }
        .support-item {
            background-color: #fdfdfd;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            padding: 15px 20px;
        }
        .support-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .support-subject {
            font-size: 1.1em;
            font-weight: bold;
            color: #0056b3;
        }
        .support-status {
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            color: #fff;
        }
        .status-moi { background-color: #007bff; }
        .status-da_xem { background-color: #ffc107; color: #333; }
        .status-da_phan_hoi { background-color: #28a745; }

        .support-body {
            font-size: 0.95em;
            color: #333;
            line-height: 1.6;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ddd;
            white-space: pre-wrap; 
        }
        .support-date {
            font-size: 0.85em;
            color: #777;
            margin-top: 10px;
        }
        .support-reply {
            margin-top: 15px;
            background-color: #f4f4f4;
            border-radius: 5px;
            padding: 15px;
            border-left: 4px solid #28a745;
        }
        .reply-header {
            font-weight: bold;
            color: #333;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .reply-header i {
            color: #28a745;
        }
        .reply-body {
            font-size: 0.95em;
            color: #333;
            line-height: 1.6;
            white-space: pre-wrap; 
        }
        .reply-date {
            font-size: 0.85em;
            color: #777;
            margin-top: 10px;
            text-align: right;
        }

        /* Bố cục Widget */
        .tk-widget { 
            background-color: #fff;
            border-radius: 8px;
            padding: 20px 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .tk-widget h3 {
            text-align: left;
            font-size: 20px;
            margin-bottom: 15px;
            color: #b50202;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-top: 0; 
        }
        
        .tk-wrapper {
            display: flex;
            gap: 30px;
            align-items: flex-start; 
        }
        .tk-main {
            flex: 2; 
            min-width: 0; 
        }
        .tk-sidebar {
            flex: 1; 
            min-width: 320px; 
            position: sticky; 
            top: 20px;
        }
        
        .post-status {
            padding: 3px 8px;
            font-size: 0.8em;
            font-weight: bold;
            color: #fff;
            border-radius: 10px;
            margin-left: 10px;
        }
        .status-published { background-color: #28a745; }
        .status-draft { background-color: #6c757d; }

        /* === BẮT ĐẦU RESPONSIVE === */
        
        @media (max-width: 992px) {
            .tk-wrapper {
                flex-direction: column; 
            }
            .tk-sidebar {
                width: 100%;
                min-width: 100%;
                position: static; 
            }
             .nav-bar .ochuathongti ul {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            /* Responsive cho Header */
            .header-top .ochuathongti {
                flex-direction: column;
                gap: 15px;
            }
            .logo {
                flex-direction: column;
                text-align: center;
            }
            .logo-img { margin-right: 0; margin-bottom: 10px; }
            .logo-text h1 { font-size: 1.2em; line-height: 1.3; }
            .header-phai { flex-direction: column; gap: 10px; }

            /* Responsive cho Nav-bar */
            .nav-bar .ochuathongti ul {
                flex-direction: column;
            }
            .nav-bar .ochuathongti ul li a {
                text-align: center;
                padding: 12px 10px;
            }
            .timkiem-box {
                width: 90%;
                margin: 5px auto;
            }
            .nav-bar .ochuathongti ul li a.timkiemnangcao {
                background-color: #9a0202;
                margin: 5px auto;
                width: 90%;
                box-sizing: border-box;
                border-radius: 5px;
            }
            
            /* Responsive cho Footer */
            .footer-top .ochuathongti {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            .footer-info { margin: 0; }
            
            /* Responsive cho trang tài khoản */
            main .content-wrapper { padding: 15px; }
            .tk-widget { padding: 15px; }
            .tk-sidebar { min-width: 100%; }
            .tk-wrapper { gap: 20px; }
            
            .info-row {
                flex-direction: column;
                gap: 5px;
                padding: 10px 0;
            }
            .info-row strong { flex-basis: auto; }
            
            .post-list-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .post-list-actions { margin-left: 0; }
            .post-list-thumb { width: 100%; height: 150px; } 
            
            .support-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="header-top">
            <div class="ochuathongti">
                <div class="logo">
                    <img src="image/AVATAR.png" alt="Logo" class="logo-img">
                    <div class="logo-text">
                        <span>CỔNG THÔNG TIN</span>
                        <h1>TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</h1>
                    </div>
                </div>
                <div class="header-phai">
                    <select name="language" class="language-select">
                        <option value="vi">Tiếng Việt</option>
                        <option value="en">English</option>
                    </select>

                    <div class="auth-links">
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <span>Chào, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</span> | 
                            <a href="logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="dangnhap.php">Đăng nhập</a> | <a href="dangky.php">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <nav class="nav-bar">
            <div class="ochuathongti">
                <ul>
                    <li><a href="trangchu.php"><i class="fas fa-home"></i></a></li>
                    <li><a href="gioithieu.php">Giới thiệu</a></li>
                    <li><a href="thongtina50.php">Thông tin A50</a></li>
                    <li><a href="thongtina80.php">Thông tin A80</a></li>
                    <li><a href="congdong.php">Cộng đồng</a></li>
                    <li><a href="hotro.php">Hỗ trợ</a></li>
                    <li>
                        <div class="timkiem-box">
                            <form class="timkiem-box" action="https://www.google.com/search" method="get" target="_blank">   
                                <input type="text" name="q" placeholder="Nhập từ khoá tìm kiếm" required>
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                    </li>
                    <li><a href="taikhoan.php" class="timkiemnangcao" style="background-color: #9a0202;">Thông tin tài khoản</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="ochuathongti content-wrapper tk-wrapper">

            <div class="tk-main">
                
                <?php if (!empty($message)): ?>
                    <div class="form-message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="tk-widget">
                    <h3><i class="fas fa-list-alt"></i> Bài viết cộng đồng đã đăng</h3>
                    
                    <a href="dangbai.php" class="add-post-btn"> <i class="fas fa-plus"></i> Đăng bài mới
                    </a>

                    <div class="post-list">
                        <?php if (empty($user_posts)): ?>
                            <p class="no-posts-message">Bạn chưa đăng bài viết nào.</p>
                        <?php else: ?>
                            <?php foreach ($user_posts as $post): ?>
                                <div class="post-list-item">
                                    <div class="post-list-thumb">
                                        <?php if (!empty($post['duong_dan_media'])): // Thêm kiểm tra
                                            if ($post['loai_media'] == 'video'): ?>
                                                <video src="<?php echo htmlspecialchars($post['duong_dan_media']); ?>#t=0.5" preload="metadata"></video>
                                            <?php else: ?>
                                                <img src="<?php echo htmlspecialchars($post['duong_dan_media']); ?>" alt="Thumbnail">
                                            <?php endif; 
                                        endif; ?>
                                    </div>
                                    <div class="post-list-info">
                                        <h4 class="post-list-title">
                                            <a href="congdong.php?id=<?php echo $post['id']; ?>">
                                                <?php echo htmlspecialchars($post['tieu_de']); ?>
                                            </a>
                                        </h4>
                                        <span class="post-list-date">
                                            <i class="fas fa-calendar-alt"></i> <?php echo date("d/m/Y", strtotime($post['ngay_tao'])); ?>
                                        </span>
                                    </div>
                                    <div class="post-list-actions">
                                        <form action="xoabai.php" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không? Hành động này không thể hoàn tác.');">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <button type="submit" class="btn-delete">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($is_admin): ?>
                    <div class="tk-widget">
                        <h3><i class="fas fa-file-signature"></i> Bài viết chính thức đã đăng</h3>
                        
                        <a href="admin_vietbai.php" class="add-post-btn">
                            <i class="fas fa-plus"></i> Viết bài mới
                        </a>

                        <div class="post-list">
                            <?php if (empty($admin_articles)): ?>
                                <p class="no-posts-message">Bạn chưa đăng bài viết chính thức nào.</p>
                            <?php else: ?>
                                <?php foreach ($admin_articles as $article): ?>
                                    <div class="post-list-item">
                                        <div class="post-list-info">
                                            <h4 class="post-list-title">
                                                <a href="congdong.php?id=<?php echo $article['id']; ?>&type=article">
                                                    <?php echo htmlspecialchars($article['tieu_de']); ?>
                                                </a>
                                                <span class="post-status status-<?php echo $article['trang_thai']; ?>">
                                                    <?php echo translate_article_status($article['trang_thai']); ?>
                                                </span>
                                            </h4>
                                            <span class="post-list-date">
                                                <i class="fas fa-calendar-alt"></i> <?php echo date("d/m/Y", strtotime($article['ngay_tao'])); ?>
                                            </span>
                                        </div>
                                        <div class="post-list-actions">
                                            <a href="admin_suabai.php?id=<?php echo $article['id']; ?>" class="btn-update" style="padding: 5px 10px; font-size: 0.9em;">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="tk-widget">
                    <h3><i class="fas fa-envelope"></i> Yêu cầu hỗ trợ đã gửi</h3>
                    
                    <a href="hotro.php" class="add-post-btn">
                        <i class="fas fa-life-ring"></i> Gửi hỗ trợ mới
                    </a>

                    <div class="support-list">
                        <?php if (empty($support_requests)): ?>
                            <p class="no-posts-message">Bạn chưa gửi yêu cầu hỗ trợ nào.</p>
                        <?php else: ?>
                            <?php foreach ($support_requests as $request): ?>
                                <div class="support-item">
                                    <div class="support-header">
                                        <span class="support-subject"><?php echo htmlspecialchars(ucfirst($request['chu_de'])); ?></span>
                                        <span class="support-status status-<?php echo $request['trang_thai']; ?>">
                                            <?php echo translate_support_status($request['trang_thai']); ?>
                                        </span>
                                    </div>
                                    <div class="support-body">
                                        <?php echo nl2br(htmlspecialchars($request['noi_dung'])); ?>
                                    </div>
                                    <div class="support-date">
                                        <i class="fas fa-paper-plane"></i> Gửi ngày: <?php echo date("d/m/Y H:i", strtotime($request['ngay_gui'])); ?>
                                    </div>

                                    <?php if (!empty($request['noi_dung_phan_hoi'])): ?>
                                        <div class="support-reply">
                                            <div class="reply-header">
                                                <i class="fas fa-user-shield"></i> 
                                                Phản hồi từ <?php echo htmlspecialchars($request['ten_admin_phan_hoi'] ?? 'Quản trị viên'); ?>:
                                            </div>
                                            <div class="reply-body">
                                                <?php echo nl2br(htmlspecialchars($request['noi_dung_phan_hoi'])); ?>
                                            </div>
                                            <div class="reply-date">
                                                <i class="fas fa-check-circle"></i> Trả lời ngày: <?php echo date("d/m/Y H:i", strtotime($request['ngay_phan_hoi'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            
            </div> 
            
            <div class="tk-sidebar">
                
                <div class="tk-widget">
                    <img src="<?php echo $avatar_path; ?>" alt="Ảnh đại diện" class="account-avatar">
                    <h3><i class="fas fa-user-circle"></i> Thông tin tài khoản</h3>
                    <div class="account-info">
                        <div class="info-row">
                            <strong>Họ và tên:</strong>
                            <span><?php echo $fullname; ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Email:</strong>
                            <span><?php echo $email; ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Loại tài khoản:</strong>
                            <span><?php echo $role_display; ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Giới tính:</strong>
                            <span><?php echo $gender_display; ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Ngày sinh:</strong>
                            <span><?php echo $dob_display; ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Tỉnh thành:</strong>
                            <span><?php echo $location_display; ?></span>
                        </div>
                        <div class="info-row">
                            <strong>Ngày tham gia:</strong>
                            <span><?php echo $created_at_display; ?></span>
                        </div>
                        <div class="update-action">
                            <a href="capnhattaikhoan.php" class="btn-update">
                                <i class="fas fa-edit"></i> Cập nhật thông tin
                            </a>
                        </div>
                    </div>
                </div> 

                <?php if ($is_admin): ?>
                    <div class="tk-widget admin-panel-link">
                        <h2><i class="fas fa-shield-alt"></i> Bảng điều khiển Admin</h2>
                        <p>Bạn có quyền quản trị viên. Truy cập các trang quản lý.</p>
                        <div class="admin-buttons">
                            <a href="admin_duyetbai.php" class="btn-admin-duyetbai">
                                <i class="fas fa-tasks"></i> DUYỆT BÀI VIẾT
                            </a>
                            <a href="admin_hotro.php" class="btn-admin-duyetbai btn-admin-hotro">
                                <i class="fas fa-headset"></i> TRẢ LỜI HỖ TRỢ
                            </a>
                            <a href="admin_danhmuc.php" class="btn-admin-duyetbai btn-admin-danhmuc">
                                <i class="fas fa-folder-plus"></i> QUẢN LÝ DANH MỤC
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div> 
        </div> 
    </main>

    <footer>
        <div class="footer-top">
            <div class="ochuathongti">
                <img src="image/AVATAR.png" alt="Logo" class="footer-logo">
                <div class="footer-info">
                    <h4>CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</h4>
                    <p>Địa chỉ: 73 NGUYỄN HUỆ, PHƯỜI LONG CHÂU, TỈNH VĨNH LONG</p>
                    <p>Email: congthongtina80@gmail.com</p>
                    <p><a href="#">Hỗ trợ</a></p>
                </div>
                <div class="footer-cert">
                    <img src="image/AVATA A80 TRON.png" alt="NCA Logo">
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="ochuathongti">
                <p>Website được thiết kế bởi: Dương Hoàng Khải, Huỳnh Đức Huy, Lê Phương Thùy</p>
            </div>
        </div>
    </footer>
</body>
</html>