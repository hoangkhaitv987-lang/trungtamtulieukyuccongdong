<?php
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: trangchu.php");
    exit;}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
    <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
    /* --- CÀI ĐẶT CHUNG --- */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        /* background-color: #f4f4f4; (Bị ghi đè bởi ảnh nền) */
        color: #333;
        line-height: 1.6;
        /* Đảm bảo footer luôn ở cuối trang */
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        
        /* br hinh anh thay doi */
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

    main {
        flex-grow: 1; /* Đẩy footer xuống dưới */
        position: relative; /* Bổ sung z-index */
        z-index: 2;
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
    main h3 {
        color: #333; /* Đảm bảo h3 trong main có màu */
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

    /* --- HEADER --- */
    header {
        flex-shrink: 0; /* Không co header */
        position: relative; /* Bổ sung z-index */
        z-index: 2;
    }

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

    .header-phai { /* Sửa lỗi tên class từ .header-right */
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
        border-radius: 0 5px 5px 0;
    }

    .timkiemnangcao {
        display: block;
        text-align: right;
        font-weight: bold;
        color: #ccc;
    }
    /* --- MAIN CONTENT (Chung) --- */
    main {
        padding: 30px 0; /* Tăng khoảng cách cho trang đăng nhập */
    }

    .content-wrapper {
        background-color: #fff;
        padding: 20px 30px; /* Tăng padding */
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        border-radius: 8px; /* Bo góc */
        max-width: 700px; /* Giới hạn chiều rộng form */
    }

    main h3 {
        font-size: 20px;
        color: #b50202;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    main h3 i {
        margin-right: 10px;
    }
    /* =========================================
    --- CSS RIÊNG CHO TRANG ĐĂNG KÝ ---
    ========================================= */

    .register-ochuathongti { 
        /* max-width: 500px; (Đã chuyển lên .content-wrapper) */
        margin: 0 auto; /* Đã có .content-wrapper xử lý */
        padding: 0; /* Đã có .content-wrapper xử lý */
    }

    .register-ochuathongti h3 {
        text-align: center;
        font-size: 24px;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
    }

    /* Gộp chung các kiểu input và select để đồng bộ */
    .form-group input[type="text"],
    .form-group input[type="password"],
    .form-group input[type="email"],
    .form-group input[type="date"],
    .form-group input[type="file"],
    .form-group select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box; /* Quan trọng */
        font-family: Arial, sans-serif;
    }
    
    .form-group input[type="file"] {
        padding: 10px; /* Điều chỉnh padding cho file input */
        background-color: #f9f9f9;
    }

    .gender-options {
        display: flex;
        align-items: center;
        gap: 20px; /* Khoảng cách giữa "Nam" và "Nữ" */
    }

    .gender-options input[type="radio"] {
        margin-right: 5px;
    }

    .gender-options label {
        font-weight: normal; 
        margin-bottom: 0;
    }

    .register-button {
        width: 100%;
        padding: 12px;
        font-size: 18px;
        font-weight: bold;
        background-color: #b50202;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .register-button:hover {
        background-color: #9a0202;
    }

    .login-link {
        text-align: center;
        margin-top: 20px;
    }

    .login-link p {
        margin: 0;
    }

    .login-link a {
        color: #b50202;
        font-weight: bold;
    }
    
    /* CSS cho div báo lỗi (từ JS) */
    .form-error {
        color: #b50202;
        background-color: #ffe8e8;
        border: 1px solid #b50202;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
        display: none; /* Ẩn ban đầu */
    }
    /* Khi JS thêm text, nó sẽ hiện ra */
    .form-error:not(:empty) {
        display: block;
    }


    /* (Không dùng .bottom-banners) */

    /* --- FOOTER --- */
    footer {
        margin-top: auto; /* Đảm bảo footer bám dính cuối trang */
        flex-shrink: 0; /* Không co footer */
        position: relative; /* Bổ sung z-index */
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


    /* --- RESPONSIVE --- */
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
        main { padding: 15px 0; }
        .content-wrapper {
            padding: 15px;
            margin: 0 auto;
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
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                            <span>Chào, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</span> | 
                            <a href="logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="dangnhap.php">Đăng nhập</a> | 
                            <a href="dangky.php" style="color: #b50202; font-weight: bold;">Đăng ký</a>
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
                    <li><a href="taikhoan.php" class="timkiemnangcao">Thông tin tài khoản</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="ochuathongti content-wrapper">
            <div class="register-ochuathongti"> 
                <h3><i class="fas fa-user-plus"></i> Đăng ký tài khoản</h3>
                
                <div id="error-message" class="form-error">
                    </div>
                
                <form id="register-form" action="xulydangky.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="fullname">Họ và tên:</label>
                        <input type="text" id="fullname" name="fullname" placeholder="Nhập họ và tên của bạn" required>
                    </div>
                    <div class="form-group">
                        <label>Giới tính:</label>
                        <div class="gender-options">
                            <input type="radio" id="male" name="gender" value="male" required>
                            <label for="male">Nam</label>
                            <input type="radio" id="female" name="gender" value="female" required>
                            <label for="female">Nữ</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dob">Ngày tháng năm sinh:</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="location">Nơi đang sinh sống:</label>
                        <select id="location" name="location" required>
                            <option value="">-- Chọn tỉnh/thành --</option>
                            
                            <option value="hanoi">TP Hà Nội</option>
                            <option value="hue">TP Huế</option>
                            <option value="quangninh">Quảng Ninh</option>
                            <option value="caobang">Cao Bằng</option>
                            <option value="langson">Lạng Sơn</option>
                            <option value="laichau">Lai Châu</option>
                            <option value="dienbien">Điện Biên</option>
                            <option value="sonla">Sơn La</option>
                            <option value="thanhhoa">Thanh Hóa</option>
                            <option value="nghean">Nghệ An</option>
                            <option value="hatinh">Hà Tĩnh</option>
                            <option value="tuyenquang">Tuyên Quang (Tuyên Quang, Hà Giang)</option>
                            <option value="laocai">Lào Cai (Lào Cai, Yên Bái)</option>
                            <option value="thainguyen">Thái Nguyên (Thái Nguyên, Bắc Kạn)</option>
                            <option value="phutho">Phú Thọ (Phú Thọ, Vĩnh Phúc, Hòa Bình)</option>
                            <option value="bacninh">Bắc Ninh (Bắc Ninh, Bắc Giang)</option>
                            <option value="hungyen">Hưng Yên (Hưng Yên, Thái Bình)</option>
                            <option value="haiphong">TP Hải Phòng (Hải Phòng, Hải Dương)</option>
                            <option value="ninhbinh">Ninh Bình (Ninh Bình, Hà Nam, Nam Định)</option>
                            <option value="quangtri">Quảng Trị (Quảng Trị, Quảng Bình)</option>
                            <option value="danang">TP Đà Nẵng (Đà Nẵng, Quảng Nam)</option>
                            <option value="quangngai">Quảng Ngãi (Quảng Ngãi, Kon Tum)</option>
                            <option value="gialai">Gia Lai (Gia Lai, Bình Định)</option>
                            <option value="khanhhoa">Khánh Hòa (Khánh Hòa, Ninh Thuận)</option>
                            <option value="lamdong">Lâm Đồng (Lâm Đồng, Đắk Nông, Bình Thuận)</option>
                            <option value="daklak">Đắk Lắk (Đắk Lắk, Phú Yên)</option>
                            <option value="hcm">TPHCM (TPHCM, Bà Rịa - Vũng Tàu, Bình Dương)</option>
                            <option value="dongnai">Đồng Nai (Đồng Nai, Bình Phước)</option>
                            <option value="tayninh">Tây Ninh (Tây Ninh, Long An)</option>
                            <option value="cantho">TP Cần Thơ (Cần Thơ, Sóc Trăng, Hậu Giang)</option>
                            <option value="vinhlong">Vĩnh Long (Vĩnh Long, Bến Tre, Trà Vinh)</option>
                            <option value="dongthap">Đồng Tháp (Đồng Tháp, Tiền Giang)</option>
                            <option value="camau">Cà Mau (Cà Mau, Bạc Liêu)</option>
                            <option value="angiang">An Giang (An Giang, Kiên Giang)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="avatar_file">Ảnh đại diện (Không bắt buộc):</label>
                        <input type="file" id="avatar_file" name="avatar_file" accept="image/png, image/jpeg, image/gif">
                        <small style="display:block; margin-top:5px; color:#666; font-size: 0.85em;">
                            Chấp nhận .jpg, .png, .gif. Tối đa 20MB.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu:</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                    </div>
                 
                     <div class="form-group">
                        <button type="submit" class="register-button">Đăng ký</button>
                    </div>
                     <div class="login-link">
                        <p>Đã có tài khoản? <a href="dangnhap.php">Đăng nhập ngay</a></p>
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
<script>
        // (JavaScript giữ nguyên)
        window.addEventListener('DOMContentLoaded', (event) => {
            
            // 1. Lấy các tham số từ thanh URL (ví dụ: ?error=...)
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');

            // 2. Tìm đến cái div báo lỗi mà ta đã tạo ở Bước 1
            const errorDiv = document.getElementById('error-message');

            // 3. Nếu có tham số 'error' trên URL
            if (error) {
                let message = '';
                
                // 4. Chọn thông báo lỗi tương ứng
                switch (error) {
                    case 'password':
                        message = 'Lỗi: Mật khẩu xác nhận không khớp!';
                        break;
                    case 'email':
                        message = 'Lỗi: Email này đã được đăng ký. Vui lòng sử dụng email khác.';
                        break;
                    case 'unknown':
                        message = 'Đã có lỗi xảy ra. Vui lòng thử lại.';
                        break;
                    // Bạn có thể thêm các trường hợp lỗi khác ở đây
                }

                // 5. Hiển thị thông báo lỗi
                if (message) {
                    errorDiv.textContent = message;
                }
            }
        });
    </script>
</body>
</html>