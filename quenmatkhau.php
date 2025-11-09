<?php

session_start();

// Chỉ cần kết nối CSDL, không cần lấy bài đăng

require_once 'db_connect.php'; 

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quên mật khẩu - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
    <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
        <style>
        body {
     font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5; 
            color: #333;
            line-height: 1.6;
            
        }

        .ochuathongti {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        a { color: #333; text-decoration: none; }
        a:hover { color: #b50202; text-decoration: underline; }
        h1, h2, h3, h4 { margin: 0; padding: 0; }
        ul { list-style: none; margin: 0; padding: 0; }
        img { max-width: 100%; height: auto; }
        .timkiem-box { display: flex; width: 100%; }
        .timkiem-box input[type="text"] { flex-grow: 1; padding: 10px; font-size: 16px; border: 2px solid #b50202; border-right: none; border-radius: 5px 0 0 5px; }
        .timkiem-box button { padding: 0 20px; font-size: 18px; border: none; background-color: #b50202; color: #fff; cursor: pointer; border-radius: 0 5px 5px 0; }
        .timkiemnangcao { display: block; text-align: right; font-weight: bold; color: #ccc; }
        .header-top { background-color: #fff; padding: 15px 0; border-bottom: 1px solid #eee; }
        .header-top .ochuathongti { display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; }
        .logo-img { width: 60px; height: 60px; margin-right: 15px; }
        .logo-text span { font-size: 16px; color: #b50202; }
        .logo-text h1 { font-size: 24px; color: #b50202; font-weight: bold; }
        .header-phai { display: flex; align-items: center; }
        .language-select { padding: 5px; margin-right: 15px; border: 1px solid #ccc; border-radius: 4px; } 
        .auth-links a { font-weight: bold; }
        .nav-bar { background-color: #b50202; }
        .nav-bar .ochuathongti ul { display: flex; }
        .nav-bar .ochuathongti ul li a { display: block; padding: 15px 20px; color: #fff; font-weight: bold; text-decoration: none; }
        .nav-bar .ochuathongti ul li a:hover { background-color: #9a0202; color: #fff; }
        
        /* Xóa CSS Slider */
        
        /*FOOTER*/
        footer { margin-top: 20px; }
        .footer-top { background-color: #380000; color: #fff; padding: 20px 0; }
        .footer-top .ochuathongti { display: flex; justify-content: space-between; align-items: center; }
        .footer-logo { width: 80px; height: 80px; }
        .footer-info { flex-grow: 1; margin: 0 20px; }
        .footer-info h4 { font-size: 18px; margin-bottom: 10px; color: #fff; } 
        .footer-info p { margin: 5px 0; font-size: 14px; }
        .footer-info a { color: #fff; font-weight: bold; }
        .footer-cert img { height: 60px; }
        .footer-bottom { background-color: #b50202; color: #fff; padding: 15px 0; text-align: center; font-size: 13px; }
        .footer-bottom p { margin: 5px 0; }

        /* MAIN CONTENT*/
        main { padding: 40px 0; } /* Tăng padding cho main */
        .content-wrapper {
            background-color: #ffffff; 
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px; 
        }

        /* Xóa CSS các mục trang chủ (Latest Post, Info Grid, CTA, Channels...) */
        
        /*NỀN BODY */
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

        /* Xóa CSS Lightbox */
        
        /* --- RESPONSIVE --- (Giữ nguyên) */
        @media (max-width: 992px) {
            .nav-bar .ochuathongti ul { flex-wrap: wrap; }
            .logo-text h1 { font-size: 20px; }
            .nav-bar .ochuathongti ul li a { padding: 15px 12px; }
        }

        @media (max-width: 768px) {
            .header-top .ochuathongti, .footer-top .ochuathongti { 
                flex-direction: column; 
                text-align: center; 
                gap: 15px; 
            }
            .logo-text h1 { font-size: 1.2em; line-height: 1.3; }
            .header-phai { margin-top: 15px; }
            .footer-top .ochuathongti > * { margin-bottom: 15px; }

            /* Nav-bar responsive */
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
            /* Content responsive */
            .content-wrapper { padding: 15px; }
        }
        
        /* === BỔ SUNG CSS CHO FORM MỚI === */
        .form-container {
            max-width: 600px;
            margin: 0 auto; /* Tự động căn giữa */
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            color: #333; /* Đảm bảo chữ trong form có màu tối */
        }
        /* == ĐÃ SỬA LỖI CÚ PHÁP TẠI ĐÂY == */
        .form-container h3 {
            text-align: center;
            color: #b50202;
            margin-bottom: 25px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .form-group input[type="email"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Rất quan trọng để input không bị tràn */
            font-size: 16px;
        }
        .form-button {
            width: 100%;
            padding: 12px;
            background-color: #b50202;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .form-button:hover {
            background-color: #9a0202;
        }
        /* CSS cho thông báo lỗi/thành công */
        .form-message {
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
            display: none; /* Ẩn mặc định */
        }
        .form-message.error {
            display: block; /* Hiện khi có class */
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-message.success {
            display: block; /* Hiện khi có class */
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        /* == ĐÃ SỬA LỖI CÚ PHÁP TẠI ĐÂY == */
        .form-link {
            text-align: center;
            margin-top: 20px;
        }
        .form-link a {
            color: #b50202;
            font-weight: bold;
        }

    </style>
    </head>
<body>

      <header>
        <div class="header-top">
            <div class="ochuathongti" >
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
                    <li><a href="taikhoan.php" class="timkiemnangcao">Thông tin tài khoản</a></li>
                </ul>
            </div>
        </nav>
    </header>

        <main>
        <div class="ochuathongti content-wrapper">
                        <div class="form-container">
                <h3><i class="fas fa-key"></i> Xác thực tài khoản</h3>
                <p style="text-align: center; margin-bottom: 25px;">
                    Vui lòng nhập Email, Họ tên và Tỉnh thành đã đăng ký để xác thực trước khi đặt lại mật khẩu.
                </p>
                
                <form id="forgot-password-form" action="xuly_quenmatkhau.php" method="post">
                    
                                        <div id="message-div" class="form-message"></div>

                    <div class="form-group">
                        <label for="email">Email đăng ký:</label>
                        <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required>
                    </div>
                    
                                        <div class="form-group">
                        <label for="ho_ten">Họ và tên:</label>
                        <input type="text" id="ho_ten" name="ho_ten" placeholder="Nhập họ và tên đã đăng ký" required>
                    </div>

                                        <div class="form-group">
                        <label for="tinh_thanh">Tỉnh thành:</label>
                        <input type="text" id="tinh_thanh" name="tinh_thanh" placeholder="Nhập tỉnh thành đã đăng ký" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="form-button">Gửi yêu cầu</button>
                    </div>
                    
                    <div class="form-link">
                        <p>Đã nhớ mật khẩu? <a href="dangnhap.php">Đăng nhập ngay</a></p>
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
    window.addEventListener('DOMContentLoaded', (event) => {
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const success = urlParams.get('success');
        
        const messageDiv = document.getElementById('message-div');
        if (!messageDiv) return; // Dừng nếu không có div

        let message = '';
        let isError = true;

        if (error) {
            if (error === 'info_mismatch') { // Lỗi mới
                message = 'Lỗi: Thông tin Email, Họ tên, hoặc Tỉnh thành không chính xác.';
            } else if (error === 'email_not_found') {
                message = 'Lỗi: Email không tồn tại trong hệ thống.';
            } else if (error === 'mail_failed') {
                message = 'Lỗi: Không thể gửi email. Vui lòng thử lại sau.';
            } else {
                message = 'Đã xảy ra lỗi không xác định.';
            }
            messageDiv.className = 'form-message error'; // Gán class lỗi
        } 
        
        else if (success === 'email_sent') {
            message = 'Thành công! Vui lòng kiểm tra email để lấy link đặt lại mật khẩu.';
            isError = false;
            messageDiv.className = 'form-message success'; // Gán class thành công
        }

        if (message) {
            messageDiv.textContent = message;
            // Class .error hoặc .success sẽ tự động làm nó hiển thị (display: block)
        }
    });
    </script>
    </body>
</html>