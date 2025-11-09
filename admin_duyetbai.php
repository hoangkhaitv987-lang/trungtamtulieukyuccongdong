<?php
session_start();
require_once 'db_connect.php'; // Sử dụng PDO

// 1. Kiểm tra Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('location: dangnhap.php?error=noaccess');
    exit;
}

$admin_id = $_SESSION['user_id'] ?? $_SESSION['id'];

// 2. Lấy thông báo (nếu có)
$message = '';
$message_type = '';
if (isset($_SESSION['action_message'])) {
    $message = $_SESSION['action_message'];
    $message_type = $_SESSION['action_type'] ?? 'error';
    unset($_SESSION['action_message'], $_SESSION['action_type']);
}

// 3. Lấy tất cả bài viết đang chờ duyệt
$pending_posts = [];
try {
    $sql = "SELECT p.id, p.tieu_de, p.noi_dung, p.duong_dan_media, p.loai_media, p.ngay_tao, u.ho_ten AS ten_nguoi_dung
            FROM bai_dang p
            JOIN nguoi_dung u ON p.id_nguoi_dung = u.id
            WHERE p.trang_thai = 'pending'
            ORDER BY p.ngay_tao ASC";
    $stmt = $pdo->query($sql);
    $pending_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = "Lỗi khi tải bài viết: " . $e->getMessage();
    $message_type = 'error';
}

// Hàm render media (giống trang congdong.php)
function render_media($path, $type, $alt_text, $class_name = 'post-media') {
    $path_html = htmlspecialchars($path);
    $alt_html = htmlspecialchars($alt_text);
    if ($type == 'video') {
        return "<video class='{$class_name}' controls preload='metadata' src='{$path_html}#t=0.5'></video>";
    } else { 
        return "<img class='{$class_name}' src='{$path_html}' alt='{$alt_html}'>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Duyệt Bài Viết</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <style>
        /* === BẮT ĐẦU CSS CHUNG (HEADER/FOOTER/NAV) === */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
            
            /* Sticky Footer */
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 100vh; 
            
            /* Hình nền */
            background-image: url('khoanh/a80/hinhen.jpg'); 
            background-size: cover;        
            background-position: center;    
            background-attachment: fixed;   
            position: relative; 
            z-index: 1;
        }
        
        /* Lớp phủ nền */
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

        h1, h2, h3, h4 {
            margin: 0;
            padding: 0;
        }
        main h3, main h4 { color: #333; }

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
        header {
            background-color: #fff;
            position: relative; /* Đảm bảo z-index */
            z-index: 2;
        }
        .header-top {
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
            margin-top: auto; /* Sticky footer */
            flex-shrink: 0; 
            position: relative; /* Đảm bảo z-index */
            z-index: 2;
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
            color: #fff; /* Bổ sung */
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
        
        /* === KẾT THÚC CSS CHUNG === */
        
        /* === BẮT ĐẦU CSS TRANG DUYỆT BÀI === */
        
        main {
            position: relative; /* Đảm bảo main nằm trên::before */
            z-index: 2;
        }

        .admin-wrapper { 
            max-width: 900px; 
            margin: 30px auto; 
            padding: 20px; 
            background-color: #fff; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .admin-wrapper h3 { 
            text-align: center; 
            color: #b50202; 
            margin-bottom: 25px; 
            font-size: 24px; 
        }
        .post-item { 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            overflow: hidden; 
        }
        .post-header { 
            padding: 15px; 
            background-color: #f9f9f9; 
            border-bottom: 1px solid #ddd; 
        }
        .post-header h4 { 
            margin: 0; 
            font-size: 1.2em; 
            color: #0056b3; 
        }
        .post-meta { 
            font-size: 0.9em; 
            color: #555; 
        }
        .post-body { 
            padding: 15px; 
            display: flex; 
            gap: 15px; 
        }
        .post-media { 
            width: 200px; 
            height: 150px; 
            object-fit: cover; 
            border-radius: 5px; 
            flex-shrink: 0; 
        }
        .post-content { 
            font-size: 0.95em; 
            line-height: 1.6; 
            white-space: pre-wrap; 
            word-wrap: break-word; /* Thêm */
        }
        .post-actions { 
            padding: 15px; 
            background-color: #f9f9f9; 
            border-top: 1px solid #ddd; 
            display: flex; 
            justify-content: flex-end; 
            gap: 10px; 
        }
        .btn-approve, .btn-reject { 
            padding: 8px 15px; 
            border: none; 
            border-radius: 5px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            font-size: 0.9em; 
        }
        .btn-approve { 
            background-color: #28a745; 
            color: white; 
        }
        .btn-approve:hover { background-color: #218838; }
        .btn-reject { 
            background-color: #dc3545; 
            color: white; 
        }
        .btn-reject:hover { background-color: #c82333; }
        .no-posts { 
            text-align: center; 
            padding: 30px; 
            font-size: 1.1em; 
            color: #777; 
        }
        /* CSS cho thông báo */
        .form-message { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            font-weight: bold; 
            text-align: center; 
        }
        .form-message.success { 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .form-message.error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        /* === BẮT ĐẦU RESPONSIVE === */
        
        @media (max-width: 992px) {
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
            
            /* Responsive cho trang duyệt bài */
            .admin-wrapper {
                margin: 15px auto;
                padding: 10px;
            }
            .post-body {
                flex-direction: column;
            }
            .post-media {
                width: 100%;
                height: auto;
                max-height: 250px; /* Giới hạn chiều cao video */
            }
            .post-actions {
                justify-content: center;
            }
            .btn-approve, .btn-reject {
                flex-grow: 1; /* Nút bằng nhau */
                text-align: center;
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
                        <span>Chào, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</span> | 
                        <a href="logout.php">Đăng xuất</a>
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
        <div class="admin-wrapper">
            <h3><i class="fas fa-tasks"></i> Duyệt Bài Viết Chờ Đăng</h3>

            <?php if (!empty($message)): ?>
                <div class="form-message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($pending_posts)): ?>
                <p class="no-posts">Không có bài viết nào đang chờ duyệt.</p>
            <?php else: ?>
                <?php foreach ($pending_posts as $post): ?>
                    <div class="post-item">
                        <div class="post-header">
                            <h4><?php echo htmlspecialchars($post['tieu_de']); ?></h4>
                            <span class="post-meta">
                                <i class="fas fa-user"></i> Gửi bởi: <strong><?php echo htmlspecialchars($post['ten_nguoi_dung']); ?></strong>
                                | <i class="fas fa-calendar-alt"></i> Ngày: <?php echo date('d/m/Y', strtotime($post['ngay_tao'])); ?>
                            </span>
                        </div>
                        <div class="post-body">
                            <?php echo render_media($post['duong_dan_media'], $post['loai_media'], $post['tieu_de']); ?>
                            <div class="post-content">
                                <?php echo nl2br(htmlspecialchars($post['noi_dung'])); ?>
                            </div>
                        </div>
                        <div class="post-actions">
                            <form action="xuly_duyetbai.php" method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn-approve">
                                    <i class="fas fa-check"></i> Duyệt
                                </button>
                                <button type="submit" name="action" value="reject" class="btn-reject" onclick="return confirm('Bạn có chắc muốn TỪ CHỐI (XÓA) bài viết này?');">
                                    <i class="fas fa-trash"></i> Từ chối
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
</body>
</html>