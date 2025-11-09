<?php
session_start();
require_once 'db_connect.php';
// Lấy ID nếu đã đăng nhập
$user_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
}

// === CÁC HÀM HỖ TRỢ ===
function truncate_content($text, $length = 100) {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));
        $text .= '...';
    }
    return $text;
}
function render_media($path, $type, $alt_text, $class_name = 'post-media') {
    $path_html = htmlspecialchars($path);
    $alt_html = htmlspecialchars($alt_text);
    
    if (empty($path)) { // Xử lý nếu không có media
        return ''; // hoặc trả về ảnh placeholder
    }
    if ($type == 'video') {
        return "<video class='{$class_name}' controls preload='metadata' src='{$path_html}#t=0.5'></video>";
    } else { 
        return "<img class='{$class_name}' src='{$path_html}' alt='{$alt_html}'>";
    }
}
// Lấy thông báo
$success_message = '';
if (isset($_SESSION['post_success'])) {
    $success_message = $_SESSION['post_success'];
    unset($_SESSION['post_success']);
}

// === KHAI BÁO BIẾN ===
$post_id_to_show = isset($_GET['id']) ? (int)$_GET['id'] : null;
$post_type = $_GET['type'] ?? 'post'; // 'post' = bai_dang (cộng đồng), 'article' = bai_viet (admin)

$single_post = null;      // Dùng để lưu 1 bài viết chi tiết (admin hoặc user)
$posts = [];              // Dùng cho danh sách bài viết user
$featured_posts = [];     // Dùng cho bài viết user nổi bật
$all_tags = [];           // Dùng cho sidebar
$admin_articles = [];     // Dùng cho sidebar

// Biến cho chi tiết bài viết
$comments = [];
$like_count = 0;
$user_has_liked = false;

try {
    // === LOGIC XỬ LÝ ===
    if ($post_id_to_show) {
        // --- CHẾ ĐỘ XEM CHI TIẾT ---
        
        if ($post_type == 'article') {
            // === LẤY BÀI VIẾT ADMIN (bai_viet) ===
            $sql_single = "SELECT 
                                bv.id, bv.tieu_de, bv.noi_dung, 
                                bv.duong_dan_media_dai_dien AS duong_dan_media, 
                                bv.loai_media, bv.ngay_xuat_ban AS ngay_tao, 
                                nd.ho_ten AS fullname
                                FROM bai_viet bv
                                JOIN nguoi_dung nd ON bv.id_tac_gia = nd.id
                                WHERE bv.id = ? AND bv.trang_thai = 'published'";
            $stmt_single = $pdo->prepare($sql_single);
            $stmt_single->execute([$post_id_to_show]);
            $single_post = $stmt_single->fetch(PDO::FETCH_ASSOC);
            
            // Bài viết admin không có hệ thống like/comment
            $comments = [];
            $like_count = -1; // Đặt giá trị đặc biệt để ẩn
            $user_has_liked = false;

        } else {
            // === LẤY BÀI VIẾT CỘNG ĐỒNG (bai_dang) ===
            $sql_single = "SELECT p.id, p.tieu_de, p.noi_dung, p.duong_dan_media, p.loai_media, p.ngay_tao, u.ho_ten AS fullname 
                                FROM bai_dang p
                                JOIN nguoi_dung u ON p.id_nguoi_dung = u.id
                                WHERE p.id = ? AND p.trang_thai = 'approved'"; // Chỉ xem bài đã duyệt
            $stmt_single = $pdo->prepare($sql_single);
            $stmt_single->execute([$post_id_to_show]);
            $single_post = $stmt_single->fetch(PDO::FETCH_ASSOC);

            if ($single_post) {
                // Lấy like
                $stmt_likes = $pdo->prepare("SELECT COUNT(*) FROM luot_thich_bai_dang WHERE id_bai_dang = ?");
                $stmt_likes->execute([$post_id_to_show]);
                $like_count = $stmt_likes->fetchColumn();

                // Kiểm tra user đã like
                if ($user_id) {
                    $stmt_user_like = $pdo->prepare("SELECT COUNT(*) FROM luot_thich_bai_dang WHERE id_bai_dang = ? AND id_nguoi_dung = ?");
                    $stmt_user_like->execute([$post_id_to_show, $user_id]);
                    $user_has_liked = $stmt_user_like->fetchColumn() > 0;
                }
                
                // Lấy bình luận
                $sql_comments = "SELECT c.id, c.noi_dung, c.ngay_tao, u.ho_ten AS fullname 
                                    FROM binh_luan c
                                    JOIN nguoi_dung u ON c.id_nguoi_dung = u.id
                                    WHERE c.id_bai_dang = ? 
                                    ORDER BY c.ngay_tao ASC"; 
                $stmt_comments = $pdo->prepare($sql_comments);
                $stmt_comments->execute([$post_id_to_show]);
                $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } else {
        // --- CHẾ ĐỘ XEM DANH SÁCH ---
        
        // Lấy bài viết NỔI BẬT CỘNG ĐỒNG (bai_dang)
        $featured_sql = "SELECT p.id, p.tieu_de, p.duong_dan_media, p.loai_media, u.ho_ten AS fullname 
                        FROM bai_dang p
                        JOIN nguoi_dung u ON p.id_nguoi_dung = u.id
                        JOIN bai_dang_nhan pt ON p.id = pt.id_bai_dang
                        JOIN nhan t ON pt.id_nhan = t.id
                        WHERE t.slug = 'noibat' AND p.trang_thai = 'approved'
                        ORDER BY p.ngay_tao DESC 
                        LIMIT 3";
        $featured_posts = $pdo->query($featured_sql)->fetchAll(PDO::FETCH_ASSOC);

        // Lấy bài viết CỘNG ĐỒNG (bai_dang)
        $current_tag_slug = isset($_GET['tag']) ? $_GET['tag'] : '';
        $params = [];
        
        $sql_select = "SELECT p.id, p.tieu_de, p.noi_dung, p.duong_dan_media, p.loai_media, u.ho_ten AS fullname";
        $sql_from = "FROM bai_dang p JOIN nguoi_dung u ON p.id_nguoi_dung = u.id";
        $sql_where = "WHERE p.trang_thai = 'approved'"; // CHỈ HIỂN THỊ BÀI ĐÃ DUYỆT

        if (!empty($current_tag_slug)) {
            $sql_from .= " JOIN bai_dang_nhan pt ON p.id = pt.id_bai_dang
                            JOIN nhan t ON pt.id_nhan = t.id";
            $sql_where .= " AND t.slug = ?";
            $params[] = $current_tag_slug;
        }
        $sql_order = "ORDER BY p.ngay_tao DESC";
        $posts_sql = $sql_select . " " . $sql_from . " " . $sql_where . " " . $sql_order;
        $posts_stmt = $pdo->prepare($posts_sql);
        $posts_stmt->execute($params);
        $posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- LUÔN LUÔN LẤY DỮ LIỆU CHO SIDEBAR ---
    // Lấy Tags (cho sidebar)
    $tags_stmt = $pdo->query("SELECT ten AS name, slug FROM nhan ORDER BY ten ASC");
    $all_tags = $tags_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // LẤY BÀI VIẾT ADMIN (cho sidebar)
    $sql_admin = "SELECT bv.id, bv.tieu_de, bv.slug, bv.ngay_xuat_ban, bv.duong_dan_media_dai_dien, nd.ho_ten AS ten_tac_gia
                FROM bai_viet bv
                JOIN nguoi_dung nd ON bv.id_tac_gia = nd.id
                WHERE bv.trang_thai = 'published'
                ORDER BY bv.ngay_xuat_ban DESC
                LIMIT 5";
    $admin_articles = $pdo->query($sql_admin)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    echo "Lỗi CSDL: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $single_post ? htmlspecialchars($single_post['tieu_de']) : 'Cộng Đồng'; ?> - CỔNG THÔNG TIN</title>
    <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* === BẮT ĐẦU CSS GỘP TỪ FILE congdong.css === */
        /* --- CÀI ĐẶT CHUNG --- */
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

        /* 1. Header trang cộng đồng */
        .community-header {
            text-align: center;
            padding: 30px 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 10px;
            margin-bottom: 25px;
            border: 1px solid #eee;
        }

        .community-header h1 {
            font-size: 2.5em;
            color: #b50202;
            margin-bottom: 10px;
        }

        .community-header p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 25px;
        }

        .btn-submit-memory {
            background-color: #b50202;
            color: #fff;
            padding: 12px 25px;
            font-size: 1.1em;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .btn-submit-memory i {
            margin-right: 8px;
        }

        .btn-submit-memory:hover {
            background-color: #9a0202;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        /* 2. Thanh Filter */
        .filter-bar {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .filter-bar span {
            font-weight: bold;
            margin-right: 10px;
            color: #333;
        }

        .filter-bar a {
            text-decoration: none;
            color: #b50202;
            background-color: #fff;
            border: 1px solid #b50202;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .filter-bar a:hover {
            background-color: #fde8e8;
            color: #9a0202;
            text-decoration: none;
        }

        .filter-bar a.active {
            background-color: #b50202;
            color: #fff;
            font-weight: bold;
        }

        /* 3. Bài viết nổi bật */
        .featured-posts {
            margin-bottom: 30px;
        }

        .featured-posts h3 {
            font-size: 1.8em;
            color: #ffffff;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .featured-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .featured-card {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            display: block;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .featured-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        .featured-card .post-media {
            width: 100%;
            height: 220px;
            object-fit: cover; 
            display: block;
            background-color: #000; 
        }

        .featured-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0));
            padding: 30px 15px 15px 15px;
            color: #fff;
        }

        .featured-title h4 {
            font-size: 1.3em;
            margin: 0 0 5px 0;
            color: #fff;
        }

        .featured-title span {
            font-size: 0.9em;
            color: #eee;
        }


        /* 4. Grid bài viết mới */
        .new-posts h3 {
            font-size: 1.8em;
            color: #ffffff;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px; 
        }

        .post-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s ease;
        }

        .post-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .post-image-link .post-media {
            width: 100%;
            height: 200px;
            object-fit: cover; 
            display: block;
            background-color: #000;
        }

        .post-content {
            padding: 20px;
        }

        .post-title {
            font-size: 1.4em;
            margin: 0 0 10px 0;
        }

        .post-title a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }

        .post-title a:hover {
            color: #b50202;
            text-decoration: underline;
        }

        .post-snippet {
            font-size: 1em;
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
            height: 75px; 
            overflow: hidden;
        }

        .post-meta {
            border-top: 1px solid #f0f0f0;
            padding-top: 15px;
        }

        .post-author {
            font-size: 0.9em;
            color: #777;
        }

        .post-author i {
            margin-right: 5px;
            color: #b50202;
        }

        .no-posts {
            font-size: 1.2em;
            color: #777;
            text-align: center;
            padding: 50px;
            grid-column: 1 / -1; 
        }
        /* Banner dưới */
        .lienketngoai {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .lienketngoai img {
            width: 100%;
            border: 1px solid #eee;
            border-radius: 5px;
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
        /* === KẾT THÚC CSS GỘP === */


        /* === CSS CŨ CHO CHI TIẾT, LIKE, COMMENT... === */
        .form-message.success { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .post-detail-view { background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        .post-detail-view h1 { font-size: 2.2em; color: #333; margin-top: 0; margin-bottom: 10px; line-height: 1.3; }
        .post-meta-detail { font-size: 0.9em; color: #666; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .post-meta-detail span { margin-right: 20px; }
        .post-meta-detail i { margin-right: 5px; }
        .post-media-full { margin-bottom: 25px; text-align: center; }
        .post-media-full img,
        .post-media-full video { max-width: 100%; height: auto; border-radius: 8px; }
        .post-content-full { font-size: 1.1em; line-height: 1.8; color: #333; white-space: pre-wrap; word-wrap: break-word; }
        .post-actions { margin-top: 25px; padding: 20px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 20px; }
        .like-btn { background-color: #f0f2f5; border: 1px solid #ddd; border-radius: 6px; padding: 10px 15px; font-size: 1em; font-weight: bold; color: #333; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; }
        .like-btn:hover { background-color: #e4e6e9; }
        .like-btn:disabled { background-color: #f0f2f5; color: #aaa; cursor: not-allowed; }
        .like-btn .fa-heart { color: #666; transition: all 0.2s ease; }
        .like-btn.liked { background-color: #ffebee; border-color: #e57373; color: #c62828; }
        .like-btn.liked .fa-heart { color: #e53935; transform: scale(1.1); }
        .like-count { font-size: 0.9em; color: #555; font-weight: 500; }
        .comments-section { margin-top: 30px; }
        .comments-section h3 { font-size: 1.8em; color: #333; margin-bottom: 20px; border-bottom: 2px solid #9a0202; padding-bottom: 10px; }
        .comment-list { margin-bottom: 30px; display: flex; flex-direction: column; gap: 20px; }
        .comment { background-color: #f9f9f9; border: 1px solid #eee; border-radius: 8px; padding: 15px; position: relative; }
        .comment-author { font-weight: bold; color: #0056b3; font-size: 1.05em; margin-bottom: 5px; }
        .comment-date { font-size: 0.85em; color: #777; position: absolute; top: 15px; right: 15px; }
        .comment-content { font-size: 1em; line-height: 1.6; color: #333; padding-top: 5px; word-wrap: break-word; }
        .no-comments, .comment-login-prompt { font-size: 1.1em; color: #666; text-align: center; padding: 20px; background-color: #fdfdfd; border-radius: 8px; }
        .comment-login-prompt a { color: #007bff; font-weight: bold; text-decoration: none; }
        .comment-login-prompt a:hover { text-decoration: underline; }
        .comment-form h4 { font-size: 1.4em; color: #444; margin-bottom: 15px; }
        .comment-form textarea { width: 100%; box-sizing: border-box; min-height: 120px; border: 1px solid #ccc; border-radius: 6px; padding: 12px; font-size: 1em; font-family: inherit; margin-bottom: 15px; resize: vertical; }
        .comment-form button { background-color: #9a0202; color: #fff; border: none; padding: 12px 25px; font-size: 1em; font-weight: bold; border-radius: 6px; cursor: pointer; transition: background-color 0.2s ease; }
        .comment-form button:hover { background-color: #7a0101; }
        
        /* br hinh anh thay doi (GHI ĐÈ LÊN body CỦA CSS GỘP) */
        body { background-image: url('khoanh/a80/hinhen.jpg'); background-size: cover; background-position: center; background-attachment: fixed; position: relative; z-index: 1; }
        body::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: black; opacity: 0.8; z-index: -1; }

        /* === BỐ CỤC MỚI (SIDEBAR) === */
        .tk-widget { background-color: #fff; border-radius: 8px; padding: 20px 25px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .tk-widget h3 { text-align: left; font-size: 18px; margin-top: 0; margin-bottom: 20px; color: #b50202; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .cd-wrapper { display: flex; gap: 30px; align-items: flex-start; }
        .cd-main { flex: 2; min-width: 0; }
        .cd-sidebar { flex: 1; min-width: 320px; position: sticky; top: 20px; }
        .filter-bar { padding: 0; } /* Ghi đè lại filter-bar từ CSS gộp */
        .tag-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag-list a { display: inline-block; padding: 6px 12px; background-color: #f0f2f5; border-radius: 20px; font-size: 0.9em; font-weight: 500; color: #333; text-decoration: none; transition: all 0.2s ease; border: none; } /* Ghi đè border */
        .tag-list a:hover { background-color: #e0e0e0; }
        .tag-list a.active { background-color: #b50202; color: #fff; }
        .admin-articles-widget .article-list { display: flex; flex-direction: column; gap: 15px; }
        .admin-articles-widget .article-item { display: flex; align-items: center; gap: 15px; padding-bottom: 15px; border-bottom: 1px dashed #eee; }
        .admin-articles-widget .article-item:last-child { border-bottom: none; padding-bottom: 0; }
        .admin-articles-widget .article-thumb { flex-shrink: 0; width: 80px; height: 60px; border-radius: 5px; overflow: hidden; }
        .admin-articles-widget .article-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .admin-articles-widget .article-info { flex-grow: 1; }
        .admin-articles-widget .article-title { font-size: 1em; font-weight: bold; line-height: 1.4; margin: 0; }
        .admin-articles-widget .article-title a { color: #0056b3; text-decoration: none; }
        .admin-articles-widget .article-title a:hover { text-decoration: underline; }
        .admin-articles-widget .article-date { font-size: 0.85em; color: #777; margin-top: 5px; display: block; }
        .admin-articles-widget .article-date i { margin-right: 5px; }
        .admin-articles-widget .no-posts { font-size: 0.9em; color: #777; text-align: center; }
        
        /* === MEDIA QUERY CHO TABLET (Tách sidebar) === */
        @media (max-width: 992px) {
            .cd-wrapper { flex-direction: column; }
            .cd-sidebar { width: 100%; min-width: 100%; position: static; }
        }
        
        /* === MEDIA QUERY MỚI CHO RESPONSIVE ĐIỆN THOẠI === */
        @media (max-width: 768px) {
            /* --- Responsive Chung --- */
            .ochuathongti {
                width: 95%;
                padding: 0 10px;
            }

            /* --- Responsive Header --- */
            .header-top .ochuathongti {
                flex-direction: column;
                gap: 15px;
            }
            .logo {
                flex-direction: column;
                text-align: center;
            }
            .logo-img { margin-right: 0; margin-bottom: 10px; }
            .logo-text h1 { font-size: 1.5em; line-height: 1.2; }
            .header-phai { flex-direction: column; gap: 10px; }
            
            /* --- Responsive Nav --- */
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

            /* --- Responsive Main Content --- */
            .community-header h1 { font-size: 2em; }
            .featured-grid, .post-grid { gap: 15px; } /* Giảm gap */
            .lienketngoai { grid-template-columns: 1fr; }

            /* --- Responsive Detail View --- */
            .post-detail-view { padding: 20px 15px; }
            .post-detail-view h1 { font-size: 1.8em; }
            .post-meta-detail {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .post-actions {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .comment-date {
                position: static;
                margin-top: 5px;
                font-size: 0.8em;
            }
            .comments-section h3 { font-size: 1.5em; }
            .comment-form textarea { min-height: 100px; }

            /* --- Responsive Footer --- */
            .footer-top .ochuathongti {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            .footer-info { margin: 0; }
            .footer-info p { font-size: 13px; }
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
                    <li><a href="congdong.php" style="background-color: #9a0202;">Cộng đồng</a></li> 
                    <li><a href="hotro.php">Hỗ trợ</a></li>
                    <li>
                        <div class="timkiem-box">
                            <form class="timkiem-box" action="https://www.google.com/search" method="get" target="_blank"> 
                                <input type="text" name="q" placeholder="Nhập từ khoá tìm kiếm" required>
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                    </li>
                    <li><a href="taikhoan.php" class="timkiemnangcao">Thông tin tài khoản</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div class="ochuathongti content-wrapper community-page">
            
            <?php if (!$post_id_to_show): ?>
                <section class="community-header">
                    <h1><i class="fas fa-users"></i> Chia sẻ Ký Ức A80</h1>
                    <p>Nơi mọi người cùng chia sẻ cảm nghĩ, hình ảnh và câu chuyện của mình.</p>
                    <a href="dangbai.php" class="btn-submit-memory">
                        <i class="fas fa-paper-plane"></i> Gửi Ký Ức của bạn
                    </a>
                </section>
                
                <?php if (!empty($success_message)): ?>
                    <div class="form-message success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="cd-wrapper">
                
                <div class="cd-main">
                <?php if ($post_id_to_show): ?>
                    <?php if ($single_post): ?>
                        <section class="post-detail-view">
                            <a href="congdong.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
                            <h1><?php echo htmlspecialchars($single_post['tieu_de']); ?></h1>
                            <div class="post-meta-detail">
                                <span class="post-author">
                                    <i class="fas <?php echo $post_type == 'article' ? 'fa-user-shield' : 'fa-user'; ?>"></i> 
                                    <?php echo htmlspecialchars($single_post['fullname']); ?>
                                </span>
                                <span class="post-date">
                                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($single_post['ngay_tao'])); ?>
                                </span>
                            </div>
                            
                            <div class="post-media-full">
                                <?php echo render_media($single_post['duong_dan_media'], $single_post['loai_media'], $single_post['tieu_de'], 'post-media-full'); ?>
                            </div>
                            <div class="post-content-full">
                                <?php echo nl2br(htmlspecialchars($single_post['noi_dung']));  ?>
                            </div>

                            <?php if ($post_type == 'post'): ?>
                                <div class="post-actions">
                                    <button class="like-btn <?php echo $user_has_liked ? 'liked' : ''; ?>" 
                                            data-post-id="<?php echo $single_post['id']; ?>" 
                                            <?php echo !$user_id ? 'disabled' : '';  ?>
                                            >
                                        <i class="fas fa-heart"></i> 
                                        <span class="like-text"><?php echo $user_has_liked ? 'Đã thích' : 'Yêu thích'; ?></span>
                                    </button>
                                    <span class="like-count"><?php echo $like_count; ?> lượt thích</span>
                                </div>

                                <section class="comments-section">
                                    <h3><i class="fas fa-comments"></i> Bình luận (<span id="comment-count"><?php echo count($comments); ?></span>)</h3>
                                    
                                    <div class="comment-list" id="comment-list">
                                        <?php if (empty($comments)): ?>
                                            <p class="no-comments" id="no-comments-msg">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                                        <?php else: ?>
                                            <?php foreach ($comments as $comment): ?>
                                                <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                                                    <div class="comment-author"><?php echo htmlspecialchars($comment['fullname']); ?></div>
                                                    <div class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['ngay_tao'])); ?></div>
                                                    <div class="comment-content"><?php echo nl2br(htmlspecialchars($comment['noi_dung'])); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($user_id): ?>
                                        <form class="comment-form" id="comment-form">
                                            <h4>Gửi bình luận của bạn</h4>
                                            <textarea id="comment-content" name="content" placeholder="Viết bình luận..." required></textarea>
                                            <input type="hidden" name="post_id" value="<?php echo $single_post['id']; ?>">
                                            <button type="submit">Gửi</button>
                                        </form>
                                    <?php else: ?>
                                        <p class="comment-login-prompt">
                                            <a href="dangnhap.php?redirect=<?php echo urlencode('congdong.php?id=' . $post_id_to_show); ?>">Đăng nhập</a> để bình luận.
                                        </p>
                                    <?php endif; ?>
                                </section>
                            <?php endif; // Kết thúc if ($post_type == 'post') ?>
                        </section>
                    <?php else: ?>
                        <section class="post-detail-view">
                                <a href="congdong.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
                                <h1>Không tìm thấy bài viết</h1>
                                <p>Bài viết bạn đang tìm không tồn tại, đã bị xóa, hoặc đang chờ duyệt.</p>
                        </section>
                    <?php endif; ?>

                <?php else: ?>
                    <?php if (!empty($featured_posts) && empty($current_tag_slug)): ?>
                    <section class="featured-posts">
                        <h3><i class="fas fa-star"></i> Bài viết cộng đồng nổi bật</h3>
                        <div class="featured-grid">
                            <?php foreach ($featured_posts as $post): ?>
                                <a href="congdong.php?id=<?php echo $post['id']; ?>" class="featured-card">
                                    <?php echo render_media($post['duong_dan_media'], $post['loai_media'], $post['tieu_de']); ?>
                                    <div class="featured-title">
                                        <h4><?php echo htmlspecialchars($post['tieu_de']); ?></h4>
                                        <span><?php echo htmlspecialchars($post['fullname']); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <section class="new-posts">
                        <h3><i class="fas fa-th-large"></i> 
                            <?php 
                                if (empty($current_tag_slug)) {
                                    echo 'Bài viết cộng đồng mới nhất';
                                } else {
                                    $tagName = '';
                                    foreach($all_tags as $tag) {
                                        if($tag['slug'] == $current_tag_slug) $tagName = $tag['name'];
                                    }
                                    echo 'Kết quả cho #' . htmlspecialchars($tagName);
                                }
                            ?>
                        </h3>        
                        <div class="post-grid">
                            <?php if (count($posts) > 0): ?>
                                <?php foreach ($posts as $post): ?>
                                    <div class="post-card">
                                        <a href="congdong.php?id=<?php echo $post['id']; ?>" class="post-image-link">
                                            <?php echo render_media($post['duong_dan_media'], $post['loai_media'], $post['tieu_de']); ?>
                                        </a>
                                        <div class="post-content">
                                            <h4 class="post-title">
                                                <a href="congdong.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['tieu_de']); ?></a>
                                            </h4>
                                            <p class="post-snippet">
                                                <?php echo htmlspecialchars(truncate_content($post['noi_dung'], 120)); ?>
                                            </p>
                                            <div class="post-meta">
                                                <span class="post-author">
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['fullname']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-posts">Chưa có bài viết nào<?php echo empty($current_tag_slug) ? '' : ' với thẻ này'; ?>.</p>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>
                </div> 
                <aside class="cd-sidebar">
                    
                    <section class="tk-widget filter-bar">
                        <h3><i class="fas fa-tags"></i> Lọc theo thẻ</h3>
                        <div class="tag-list">
                            <a href="congdong.php" class="<?php echo empty($current_tag_slug) ? 'active' : ''; ?>">Tất cả</a>
                            <?php foreach ($all_tags as $tag): ?>
                                <a href="congdong.php?tag=<?php echo htmlspecialchars($tag['slug']); ?>" 
                                   class="<?php echo $current_tag_slug == $tag['slug'] ? 'active' : ''; ?>">
                                    #<?php echo htmlspecialchars($tag['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="tk-widget admin-articles-widget">
                        <h3><i class="fas fa-star"></i> Tin từ Đội ngũ A80</h3>
                        <div class="article-list">
                            <?php if (empty($admin_articles)): ?>
                                <p class="no-posts" style="text-align: left; padding: 0;">Chưa có tin bài chính thức nào.</p>
                            <?php else: ?>
                                <?php foreach ($admin_articles as $article): ?>
                                    <div class="article-item">
                                        <?php if (!empty($article['duong_dan_media_dai_dien'])): ?>
                                            <a href="congdong.php?id=<?php echo $article['id']; ?>&type=article" class="article-thumb">
                                                <img src="<?php echo htmlspecialchars($article['duong_dan_media_dai_dien']); ?>" alt="Ảnh">
                                            </a>
                                        <?php endif; ?>
                                        <div class="article-info">
                                            <h4 class="article-title">
                                                <a href="congdong.php?id=<?php echo $article['id']; ?>&type=article">
                                                    <?php echo htmlspecialchars($article['tieu_de']); ?>
                                                </a>
                                            </h4>
                                            <span class="article-date">
                                                <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($article['ten_tac_gia']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                </aside> 
            </div> 
        </div> 
    </main>

    <footer>
        <div class="footer-top">
            <div class="ochuathongti">
                <img src="image/AVATAR.png" alt="Logo" class="footer-logo">
                <div class="footer-info">
                    <h4>CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</h4>
                    <p>Địa chỉ: 73 NGUYỄN HUỆ, PHƯỜNG LONG CHÂU, TỈNH VĨNH LONG</p>
                    <p>Email: congthongtina80@gmail.com</p>
                    <p><a href="hotro.php">Hỗ trợ</a></p>
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
<script>
    // (JavaScript cho Like và Comment giữ nguyên)
    const isLoggedIn = <?php echo json_encode($user_id !== null); ?>;
    const currentUserFullname = <?php echo json_encode($_SESSION['fullname'] ?? 'Người dùng'); ?>;
    // Biến mới để biết đang xem loại bài viết nào
    const currentPostType = <?php echo json_encode($post_type); ?>;

    document.addEventListener('DOMContentLoaded', () => {
        // Chỉ chạy JS này nếu là bài viết cộng đồng (post)
        if (currentPostType === 'post') {
            if (document.querySelector('.like-btn')) {
                handleLikeButton();
            }
            if (document.getElementById('comment-form')) {
                handleCommentForm();
            }
        }
    });

    function handleLikeButton() {
        const likeBtn = document.querySelector('.like-btn');
        if (!likeBtn) return;

        likeBtn.addEventListener('click', async () => {
            if (!isLoggedIn) {
                alert('Bạn cần đăng nhập để thực hiện hành động này.');
                window.location.href = 'dangnhap.php?redirect=<?php echo urlencode("congdong.php?id=" . $post_id_to_show); ?>';
                return;
            }

            const postId = likeBtn.dataset.postId;
            
            try {
                const formData = new FormData();
                formData.append('post_id', postId);

                const response = await fetch('handle_like.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                const result = await response.json();

                if (result.success) {
                    const likeCountSpan = document.querySelector('.like-count');
                    const likeTextSpan = likeBtn.querySelector('.like-text');
                    
                    likeCountSpan.textContent = `${result.new_count} lượt thích`;
                    
                    if (result.liked) {
                        likeBtn.classList.add('liked');
                        likeTextSpan.textContent = 'Đã thích';
                    } else {
                        likeBtn.classList.remove('liked');
                        likeTextSpan.textContent = 'Yêu thích';
                    }
                } else {
                    alert(result.message || 'Có lỗi xảy ra.');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('Không thể kết nối đến máy chủ. Vui lòng thử lại.');
            }
        });
    }

    function handleCommentForm() {
        const commentForm = document.getElementById('comment-form');
        if (!commentForm) return;

        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            const textarea = document.getElementById('comment-content');
            const content = textarea.value.trim();
            const postId = commentForm.querySelector('input[name="post_id"]').value;

            if (content === '') {
                alert('Vui lòng nhập nội dung bình luận.');
                textarea.focus();
                return;
            }

            try {
                const formData = new FormData();
                formData.append('post_id', postId);
                formData.append('content', content);
                
                const response = await fetch('handle_comment.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                const result = await response.json();

                if (result.success) {
                    textarea.value = '';
                    const commentList = document.getElementById('comment-list');
                    const noCommentsMsg = document.getElementById('no-comments-msg');
                    if (noCommentsMsg) {
                        noCommentsMsg.remove();
                    }
                    
                    const newCommentEl = document.createElement('div');
                    newCommentEl.className = 'comment';
                    newCommentEl.id = `comment-${result.comment.id}`;
                    
                    newCommentEl.innerHTML = `
                        <div class="comment-author">${htmlspecialchars(result.comment.fullname)}</div>
                        <div class="comment-date">${result.comment.ngay_tao}</div> 
                        <div class="comment-content">${nl2br(htmlspecialchars(result.comment.content))}</div>
                    `;
                    commentList.appendChild(newCommentEl); 
                    
                    const commentCountSpan = document.getElementById('comment-count');
                    const currentCount = parseInt(commentCountSpan.textContent) + 1;
                    commentCountSpan.textContent = currentCount;

                } else {
                    alert(result.message || 'Có lỗi xảy ra khi gửi bình luận.');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('Không thể kết nối đến máy chủ. Vui lòng thử lại.');
            }
        });
    }

    function htmlspecialchars(str) {
        if (typeof str !== 'string') return '';
        return str.replace(/[&<>"']/g, function(match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[match];
        });
    }
    
    function nl2br(str) {
        return str.replace(/(\r\n|\n\r|\r|\n)/g, '<br>');
    }
</script>

</body>
</html>