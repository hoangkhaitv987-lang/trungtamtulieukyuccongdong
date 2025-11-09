<?php
session_start();
require_once 'db_connect.php';

// 1. Kiểm tra Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('location: dangnhap.php?error=noaccess');
    exit;
}

// 2. Lấy thông báo
$message = '';
$message_type = '';
if (isset($_SESSION['action_message'])) {
    $message = $_SESSION['action_message'];
    $message_type = $_SESSION['action_type'] ?? 'error';
    unset($_SESSION['action_message'], $_SESSION['action_type']);
}

// 3. Lấy tất cả danh mục hiện có
$categories = [];
try {
    $sql_cat = "SELECT id, ten, mo_ta FROM danh_muc ORDER BY ten ASC";
    $categories = $pdo->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = "Lỗi khi tải danh mục: " . $e->getMessage();
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản Lý Danh Mục</title>
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
        
        /* === BẮT ĐẦU CSS TRANG DANH MỤC === */
        
        /* CSS Ghi đè cho trang này */
        /* body { background-color: #f0f2f5; } (ĐÃ XÓA ĐỂ GIỮ HÌNH NỀN) */
        main { position: relative; z-index: 2; }
        
        .admin-wrapper {
            max-width: 700px;
            margin: 30px auto;
            padding: 25px 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            position: relative;
            z-index: 2;
        }
        .admin-wrapper h3 {
            text-align: center;
            color: #b50202;
            margin-bottom: 25px;
            font-size: 24px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 1em;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
            font-family: Arial, sans-serif;
            font-size: 1em;
        }
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        .btn-submit-article {
            background-color: #007bff;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .btn-submit-article:hover { background-color: #0056b3; }
        .form-message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; text-align: center; }
        .form-message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .form-message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* CSS cho danh sách danh mục */
        .category-list { margin-top: 30px; border-top: 2px solid #f0f0f0; padding-top: 20px; }
        .category-list h3 {
             color: #333; /* Sửa lại màu cho h3 trong list */
             text-align: left;
             font-size: 1.3em;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px dashed #ddd;
        }
        .category-item:last-child { border-bottom: none; }
        .category-info {
             flex-grow: 1; /* Cho phép co giãn */
             padding-right: 15px; /* Khoảng cách với nút xóa */
        }
        .category-info strong {
            font-size: 1.1em;
            color: #333;
            display: block;
        }
        .category-info small {
            font-size: 0.9em;
            color: #777;
            word-break: break-word; /* Chống vỡ layout */
        }
        .btn-delete {
            padding: 5px 10px;
            background: #dc3545; 
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s ease;
            flex-shrink: 0; /* Không co nút */
        }
        .btn-delete:hover { background: #c82333; }
        
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
            
            /* Responsive cho trang Danh mục */
            main { padding: 10px 0; }
            .admin-wrapper {
                margin: 15px auto;
                padding: 15px;
            }
            .admin-wrapper h3 { font-size: 1.5em; }
            
            .category-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .category-info { padding-right: 0; }
            .btn-delete {
                align-self: flex-end; /* Đẩy nút xóa sang phải */
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
            <h3><i class="fas fa-folder-plus"></i> Quản Lý Danh Mục Bài Viết</h3>

            <?php if (!empty($message)): ?>
                <div class="form-message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="xuly_danhmuc.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="ten">Tên Danh mục mới:</label>
                    <input type="text" id="ten" name="ten" placeholder="Ví dụ: Tin tức A80, Giới thiệu..." required>
                </div>
                <div class="form-group">
                    <label for="mo_ta">Mô tả (Không bắt buộc):</label>
                    <textarea id="mo_ta" name="mo_ta" rows="3" placeholder="Mô tả ngắn về danh mục này..."></textarea>
                </div>
                <button type="submit" class="btn-submit-article">
                    <i class="fas fa-plus-circle"></i> Tạo Danh Mục
                </button>
            </form>

            <div class="category-list">
                <h3><i class="fas fa-list-ul"></i> Danh mục hiện có</h3>
                <?php if (empty($categories)): ?>
                    <p style="text-align: center; color: #777;">Chưa có danh mục nào.</p>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-item">
                            <div class="category-info">
                                <strong><?php echo htmlspecialchars($cat['ten']); ?></strong>
                                <?php if (!empty($cat['mo_ta'])): ?>
                                    <small><?php echo htmlspecialchars($cat['mo_ta']); ?></small>
                                <?php endif; ?>
                            </div>
                            <form action="xuly_danhmuc.php" method="POST" 
                                  onsubmit="return confirm('CẢNH BÁO: Xóa danh mục này cũng sẽ XÓA TẤT CẢ các bài viết chính thức thuộc danh mục này. Bạn có chắc chắn muốn xóa?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_danh_muc" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="btn-delete">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
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