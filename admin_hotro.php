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

// 3. Lấy tất cả yêu cầu hỗ trợ CHƯA PHẢN HỒI
$support_requests = [];
try {
    // Lấy các yêu cầu MỚI hoặc ĐÃ XEM
    $sql = "SELECT id, ho_ten, email, chu_de, noi_dung, trang_thai, ngay_gui 
            FROM yeu_cau_ho_tro 
            WHERE trang_thai != 'da_phan_hoi'
            ORDER BY ngay_gui ASC";
    $stmt = $pdo->query($sql);
    $support_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tự động chuyển 'moi' thành 'da_xem' khi admin tải trang
    $sql_update_status = "UPDATE yeu_cau_ho_tro SET trang_thai = 'da_xem' WHERE trang_thai = 'moi'";
    $pdo->query($sql_update_status);

} catch (Exception $e) {
    $message = "Lỗi khi tải yêu cầu hỗ trợ: " . $e->getMessage();
    $message_type = 'error';
}

// (Hàm dịch trạng thái từ taikhoan.php)
function translate_support_status($status) {
    switch ($status) {
        case 'moi': return 'Mới';
        case 'da_xem': return 'Đã xem';
        case 'da_phan_hoi': return 'Đã phản hồi';
        default: return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Trả Lời Hỗ Trợ</title>
    <!-- <link rel="stylesheet" href="trangchu.css"> ĐÃ XÓA -->
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
        
        /* === BẮT ĐẦU CSS TRANG HỖ TRỢ ADMIN === */
        
        main {
            position: relative;
            z-index: 2;
        }

        .admin-wrapper { 
            max-width: 900px; 
            margin: 30px auto; 
            padding: 20px; 
            background-color: #fff; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            position: relative;
            z-index: 2;
        }
        .admin-wrapper h3 { 
            text-align: center; 
            color: #b50202; 
            margin-bottom: 25px; 
            font-size: 24px; 
        }
        
        /* Bố cục "Khoa học" mới */
        .support-item { 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            background-color: #fff;
            overflow: hidden; /* Đảm bảo border-radius */
        }
        .support-header { 
            padding: 15px; 
            background-color: #f9f9f9; 
            border-bottom: 1px solid #ddd; 
        }
        .support-header-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .support-subject { 
            font-size: 1.2em; 
            color: #0056b3; 
            font-weight: bold; 
        }
        .support-status { 
            font-weight: bold; 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 0.85em; 
            color: #fff; 
            flex-shrink: 0; /* Không co lại */
            margin-left: 10px;
        }
        .status-moi { background-color: #007bff; }
        .status-da_xem { background-color: #ffc107; color: #333; }
        
        .support-meta { 
            font-size: 0.9em; 
            color: #555; 
            margin-top: 8px; 
        }
        
        /* Bố cục 2 cột */
        .support-content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 50% 50% */
            gap: 0;
        }
        
        .support-body { 
            padding: 15px; 
            font-size: 0.95em; 
            line-height: 1.6; 
            white-space: pre-wrap; 
            border-right: 1px solid #eee; /* Đường kẻ phân cách */
            background-color: #fdfdfd;
            word-wrap: break-word;
        }
        
        .support-reply-form { 
            padding: 15px; 
            background-color: #fff;
        }
        .support-reply-form textarea { 
            width: 100%; 
            min-height: 150px; /* Tăng chiều cao */
            padding: 10px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            box-sizing: border-box; 
            font-family: Arial, sans-serif; 
            font-size: 1em; 
            margin-bottom: 10px; 
            resize: vertical;
        }
        .btn-reply { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            font-size: 0.95em; 
            background-color: #28a745; 
            color: white; 
            width: 100%; /* Nút full-width */
        }
        .btn-reply:hover { background-color: #218838; }
        .btn-reply i { margin-right: 8px; }
        
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
            
            /* Responsive cho trang Hỗ trợ Admin */
            .admin-wrapper {
                margin: 15px auto;
                padding: 10px;
            }
            
            .support-header-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .support-subject { font-size: 1.1em; }
            .support-status { margin-left: 0; }
            
            /* Chuyển 2 cột thành 1 cột */
            .support-content-wrapper {
                grid-template-columns: 1fr;
            }
            .support-body {
                border-right: none;
                border-bottom: 1px solid #eee; /* Thêm kẻ ngang */
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
        <div class="admin-wrapper">
            <h3><i class="fas fa-headset"></i> Trả Lời Hỗ Trợ</h3>

            <?php if (!empty($message)): ?>
                <div class="form-message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($support_requests)): ?>
                <p class="no-posts">Không có yêu cầu hỗ trợ nào đang chờ.</p>
            <?php else: ?>
                <?php foreach ($support_requests as $req): ?>
                    <div class="support-item">
                        <div class="support-header">
                            <div class="support-header-top">
                                <span class="support-subject"><?php echo htmlspecialchars(ucfirst($req['chu_de'])); ?></span>
                                <span class="support-status status-<?php echo $req['trang_thai']; ?>">
                                    <?php echo translate_support_status($req['trang_thai']); ?>
                                </span>
                            </div>
                            <div class="support-meta">
                                <i class="fas fa-user"></i> Từ: <strong><?php echo htmlspecialchars($req['ho_ten']); ?></strong>
                                (<?php echo htmlspecialchars($req['email']); ?>)
                                | <i class="fas fa-calendar-alt"></i> Ngày: <?php echo date('d/m/Y', strtotime($req['ngay_gui'])); ?>
                            </div>
                        </div>
                        
                        <!-- BỐ CỤC 2 CỘT MỚI -->
                        <div class="support-content-wrapper">
                            <div class="support-body">
                                <?php echo nl2br(htmlspecialchars($req['noi_dung'])); ?>
                            </div>
                            <div class="support-reply-form">
                                <form action="xuly_admin_hotro.php" method="POST">
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <textarea name="noi_dung_phan_hoi" placeholder="Nhập nội dung phản hồi tại đây..." required></textarea>
                                    <button type="submit" class="btn-reply">
                                        <i class="fas fa-paper-plane"></i> Gửi Phản Hồi
                                    </button>
                                </form>
                            </div>
                        </div>
                        <!-- KẾT THÚC BỐ CỤC 2 CỘT -->
                        
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