<?php
session_start();
require_once 'db_connect.php'; // Sử dụng PDO

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: dangnhap.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['id'];

// 2. Lấy thông tin hiện tại của user để điền vào form
try {
    $sql = "SELECT ho_ten, email, gioi_tinh, ngay_sinh, tinh_thanh, duong_dan_avatar 
            FROM nguoi_dung WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Nếu không tìm thấy user, đăng xuất cho an toàn
        session_destroy();
        header('location: dangnhap.php?error=user_not_found');
        exit;
    }

} catch (Exception $e) {
    die("Lỗi: Không thể lấy thông tin tài khoản. " . $e->getMessage());
}

// 3. Lấy thông báo (nếu có lỗi/thành công từ trang xử lý)
$message = '';
$message_type = '';
if (isset($_SESSION['update_message'])) {
    $message = $_SESSION['update_message'];
    $message_type = $_SESSION['update_type'] ?? 'error';
    unset($_SESSION['update_message'], $_SESSION['update_type']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật tài khoản - CỔNG THÔNG TIN</title>
        <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* === BẮT ĐẦU CSS CHUNG (HEADER/FOOTER/NAV) === */
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
        
        /* --- HÌNH NỀN --- */
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

        /* === KẾT THÚC CSS CHUNG === */
        
        /* === BẮT ĐẦU CSS TRANG CẬP NHẬT === */
        
        main {
             position: relative; /* Đảm bảo main nằm trên::before */
             z-index: 2;
        }
        
        /* Khung nội dung trắng */
        main .content-wrapper {
            background-color: #fff;
            padding: 20px 25px;
            margin-top: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            max-width: 700px; /* Giới hạn chiều rộng form */
        }
        
        
        .register-ochuathongti h3 {
            text-align: center;
            color: #b50202;
            font-size: 1.8em;
            margin-bottom: 25px;
        }
        
        /* Form chung */
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group input[type="file"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .form-group input[type="file"] {
             padding: 10px;
             background-color: #f9f9f9;
        }

        /* Giới tính */
        .gender-options {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .gender-options label {
            font-weight: normal;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Nút Đăng ký */
        .register-button {
            background-color: #b50202;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
            width: 100%; /* Nút full-width */
        }
        .register-button:hover {
            background-color: #9a0202;
        }

        /* CSS cho ảnh đại diện xem trước (từ style inline) */
        .avatar-preview {
            display: block;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px auto;
            border: 3px solid #ddd;
        }
        .form-helper-text {
             display:block; 
             margin-top:5px; 
             color:#666; 
             font-size: 0.85em;
        }
        /* CSS cho thông báo (từ style inline) */
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
            
            /* Responsive cho Form */
            main .content-wrapper {
                padding: 15px;
                margin-top: 15px;
                margin-bottom: 15px;
            }
            .register-ochuathongti h3 {
                font-size: 1.5em;
            }
            .gender-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
        <div class="ochuathongti content-wrapper">
            <div class="register-ochuathongti">
                <h3><i class="fas fa-edit"></i> Cập nhật thông tin tài khoản</h3>
                
                <?php if (!empty($message)): ?>
                    <div class="form-message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form id="update-form" action="xuly_capnhattaikhoan.php" method="post" enctype="multipart/form-data">
                    
                    <img src="<?php echo htmlspecialchars($user['duong_dan_avatar'] ?? 'image/avatar_default.png'); ?>" alt="Ảnh đại diện" class="avatar-preview" id="avatarPreview">

                    <div class="form-group">
                        <label for="avatar_file">Thay đổi ảnh đại diện:</label>
                        <input type="file" id="avatar_file" name="avatar_file" accept="image/png, image/jpeg, image/gif">
                        <small class="form-helper-text">Để trống nếu không muốn thay đổi. (Tối đa 20MB)</small>
                    </div>

                    <div class="form-group">
                        <label for="fullname">Họ và tên:</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Giới tính:</label>
                        <div class="gender-options">
                            <input type="radio" id="male" name="gender" value="male" <?php echo ($user['gioi_tinh'] == 'male') ? 'checked' : ''; ?> required>
                            <label for="male">Nam</label>
                            <input type="radio" id="female" name="gender" value="female" <?php echo ($user['gioi_tinh'] == 'female') ? 'checked' : ''; ?> required>
                            <label for="female">Nữ</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="dob">Ngày tháng năm sinh:</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($user['ngay_sinh']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Nơi đang sinh sống:</label>
                    <select id="location" name="location" required>
                            <option value="">-- Chọn tỉnh/thành --</option>
                            
                            <option value="hanoi" <?php echo ($user['tinh_thanh'] == 'hanoi') ? 'selected' : ''; ?>>TP Hà Nội</option>
                            <option value="hue" <?php echo ($user['tinh_thanh'] == 'hue') ? 'selected' : ''; ?>>TP Huế</option>
                            <option value="quangninh" <?php echo ($user['tinh_thanh'] == 'quangninh') ? 'selected' : ''; ?>>Quảng Ninh</option>
                            <option value="caobang" <?php echo ($user['tinh_thanh'] == 'caobang') ? 'selected' : ''; ?>>Cao Bằng</option>
                            <option value="langson" <?php echo ($user['tinh_thanh'] == 'langson') ? 'selected' : ''; ?>>Lạng Sơn</option>
                            <option value="laichau" <?php echo ($user['tinh_thanh'] == 'laichau') ? 'selected' : ''; ?>>Lai Châu</option>
                            <option value="dienbien" <?php echo ($user['tinh_thanh'] == 'dienbien') ? 'selected' : ''; ?>>Điện Biên</option>
                            <option value="sonla" <?php echo ($user['tinh_thanh'] == 'sonla') ? 'selected' : ''; ?>>Sơn La</option>
                            <option value="thanhhoa" <?php echo ($user['tinh_thanh'] == 'thanhhoa') ? 'selected' : ''; ?>>Thanh Hóa</option>
                            <option value="nghean" <?php echo ($user['tinh_thanh'] == 'nghean') ? 'selected' : ''; ?>>Nghệ An</option>
                            <option value="hatinh" <?php echo ($user['tinh_thanh'] == 'hatinh') ? 'selected' : ''; ?>>Hà Tĩnh</option>
                            
                            <option value="tuyenquang" <?php echo ($user['tinh_thanh'] == 'tuyenquang') ? 'selected' : ''; ?>>Tuyên Quang (Tuyên Quang, Hà Giang)</option>
                            <option value="laocai" <?php echo ($user['tinh_thanh'] == 'laocai') ? 'selected' : ''; ?>>Lào Cai (Lào Cai, Yên Bái)</option>
                            <option value="thainguyen" <?php echo ($user['tinh_thanh'] == 'thainguyen') ? 'selected' : ''; ?>>Thái Nguyên (Thái Nguyên, Bắc Kạn)</option>
                            <option value="phutho" <?php echo ($user['tinh_thanh'] == 'phutho') ? 'selected' : ''; ?>>Phú Thọ (Phú Thọ, Vĩnh Phúc, Hòa Bình)</option>
                            <option value="bacninh" <?php echo ($user['tinh_thanh'] == 'bacninh') ? 'selected' : ''; ?>>Bắc Ninh (Bắc Ninh, Bắc Giang)</option>
                            <option value="hungyen" <?php echo ($user['tinh_thanh'] == 'hungyen') ? 'selected' : ''; ?>>Hưng Yên (Hưng Yên, Thái Bình)</option>
                            <option value="haiphong" <?php echo ($user['tinh_thanh'] == 'haiphong') ? 'selected' : ''; ?>>TP Hải Phòng (Hải Phòng, Hải Dương)</option>
                            <option value="ninhbinh" <?php echo ($user['tinh_thanh'] == 'ninhbinh') ? 'selected' : ''; ?>>Ninh Bình (Ninh Bình, Hà Nam, Nam Định)</option>
                            <option value="quangtri" <?php echo ($user['tinh_thanh'] == 'quangtri') ? 'selected' : ''; ?>>Quảng Trị (Quảng Trị, Quảng Bình)</option>
                            <option value="danang" <?php echo ($user['tinh_thanh'] == 'danang') ? 'selected' : ''; ?>>TP Đà Nẵng (Đà Nẵng, Quảng Nam)</option>
                            <option value="quangngai" <?php echo ($user['tinh_thanh'] == 'quangngai') ? 'selected' : ''; ?>>Quảng Ngãi (Quảng Ngãi, Kon Tum)</option>
                            <option value="gialai" <?php echo ($user['tinh_thanh'] == 'gialai') ? 'selected' : ''; ?>>Gia Lai (Gia Lai, Bình Định)</option>
                            <option value="khanhhoa" <?php echo ($user['tinh_thanh'] == 'khanhhoa') ? 'selected' : ''; ?>>Khánh Hòa (Khánh Hòa, Ninh Thuận)</option>
                            <option value="lamdong" <?php echo ($user['tinh_thanh'] == 'lamdong') ? 'selected' : ''; ?>>Lâm Đồng (Lâm Đồng, Đắk Nông, Bình Thuận)</option>
                            <option value="daklak" <?php echo ($user['tinh_thanh'] == 'daklak') ? 'selected' : ''; ?>>Đắk Lắk (Đắk Lắk, Phú Yên)</option>
                            <option value="hcm" <?php echo ($user['tinh_thanh'] == 'hcm') ? 'selected' : ''; ?>>TPHCM (TPHCM, Bà Rịa - Vũng Tàu, Bình Dương)</option>
                            <option value="dongnai" <?php echo ($user['tinh_thanh'] == 'dongnai') ? 'selected' : ''; ?>>Đồng Nai (Đồng Nai, Bình Phước)</option>
                            <option value="tayninh" <?php echo ($user['tinh_thanh'] == 'tayninh') ? 'selected' : ''; ?>>Tây Ninh (Tây Ninh, Long An)</option>
                            <option value="cantho" <?php echo ($user['tinh_thanh'] == 'cantho') ? 'selected' : ''; ?>>TP Cần Thơ (Cần Thơ, Sóc Trăng, Hậu Giang)</option>
                            <option value="vinhlong" <?php echo ($user['tinh_thanh'] == 'vinhlong') ? 'selected' : ''; ?>>Vĩnh Long (Vĩnh Long, Bến Tre, Trà Vinh)</option>
                            <option value="dongthap" <?php echo ($user['tinh_thanh'] == 'dongthap') ? 'selected' : ''; ?>>Đồng Tháp (Đồng Tháp, Tiền Giang)</option>
                            <option value="camau" <?php echo ($user['tinh_thanh'] == 'camau') ? 'selected' : ''; ?>>Cà Mau (Cà Mau, Bạc Liêu)</option>
                            <option value="angiang" <?php echo ($user['tinh_thanh'] == 'angiang') ? 'selected' : ''; ?>>An Giang (An Giang, Kiên Giang)</option>
                        </select>
                    </div>

                    <hr style="border:0; border-top: 1px solid #eee; margin: 20px 0;">
                    
                    <div class="form-group">
                        <label for="password">Mật khẩu mới:</label>
                        <input type="password" id="password" name="password" placeholder="Để trống nếu không muốn thay đổi">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="register-button">Cập nhật thông tin</button>
                    </div>
                </form>
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
        document.getElementById('avatar_file').onchange = function (evt) {
            const [file] = this.files;
            if (file) {
                document.getElementById('avatarPreview').src = URL.createObjectURL(file);
            }
        };
    </script>
</body>
</html>