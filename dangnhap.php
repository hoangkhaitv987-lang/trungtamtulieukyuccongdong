<?php
session_start();

// 1. CHUYỂN HƯỚNG NẾU ĐÃ ĐĂNG NHẬP
// Nếu người dùng đã đăng nhập, chuyển hướng họ về trang chủ
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('location: trangchu.php');
    exit;
}

// 2. THÔNG TIN KẾT NỐI DATABASE (Lấy từ file xulydangky.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trungtamtulieu"; // Giữ CSDL là 'congthongtina80' như các file trước

// Khởi tạo biến
$email = "";
$login_err = "";

// 3. XỬ LÝ FORM KHI GỬI LÊN (METHOD POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Tạo kết nối
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        $login_err = "Lỗi kết nối CSDL. Vui lòng thử lại sau.";
    } else {
        $conn->set_charset("utf8mb4");

        // Lấy dữ liệu từ form
        $email = trim($_POST['email']);
        $password_input = $_POST['password'];

        // 4. CHUẨN BỊ TRUY VẤN (Dùng prepared statement để chống SQL Injection)
        // Ta cần lấy id, fullname, và password_hash từ CSDL
        $sql = "SELECT id, ho_ten AS fullname, mat_khau_bam AS password_hash FROM nguoi_dung WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Gán email vào biến $param_email
            $stmt->bind_param("s", $param_email);
            $param_email = $email;
            
            // Thực thi
            if ($stmt->execute()) {
                $stmt->store_result();
                
                // 5. KIỂM TRA XEM CÓ TÌM THẤY EMAIL KHÔNG
                if ($stmt->num_rows == 1) {
                    // Lấy kết quả
                    $stmt->bind_result($id, $fullname, $hashed_password);
                    if ($stmt->fetch()) {
                        
                        // 6. XÁC THỰC MẬT KHẨU
                        if (password_verify($password_input, $hashed_password)) {
                            // Mật khẩu chính xác! Bắt đầu session
                            
                            // Lưu dữ liệu vào biến session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["fullname"] = $fullname;
                            $_SESSION["email"] = $email; // Lưu cả email có thể sẽ cần
                            
                            // Chuyển hướng về trang (chaomung.php hoặc trangchu.php)
                            // Giữ nguyên code của bạn là chaomung.php
                            header("location: chaomung.php");
                            exit;
                        } else {
                            // Sai mật khẩu
                            $login_err = "Email hoặc mật khẩu không chính xác.";
                        }
                    }
                } else {
                    // Không tìm thấy email
                    $login_err = "Email hoặc mật khẩu không chính xác.";
                }
            } else {
                $login_err = "Đã có lỗi xảy ra. Vui lòng thử lại sau.";
            }
            // Đóng statement
            $stmt->close();
        }
    }
    // Đóng kết nối
    $conn->close();
}
// Chuyển hướng nếu đã đăng nhập (đoạn này bạn có 2 lần, tôi giữ nguyên)
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: trangchu.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
        <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- CÀI ĐẶT CHUNG --- */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            /* background-color: #ffffff; (Bị ghi đè bởi ảnh nền) */
            color: #333;
            line-height: 1.6;
            
            /* CSS Sticky footer */
            display: grid;
            grid-template-rows: auto 1fr auto;
            min-height: 100vh; 
            
            /* CSS Nền */
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

        h1, h2, h3, h4 .chuh3 {
            margin: 0;
            padding: 0;
        }
        /* Đảm bảo h3 trong main có màu */
        main h3 {
            color: #333;
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

        /* --- FOOTER --- */
        footer {
            margin-top: auto; /* Đảm bảo footer bám dính cuối trang */
            flex-shrink: 0; /* Không co footer */
            position: relative; /* Đảm bảo z-index hoạt động */
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
        
        /* === BẮT ĐẦU CSS TRANG ĐĂNG NHẬP === */
        main {
            position: relative; /* Đảm bảo main nằm trên::before */
            z-index: 2;
        }

        /* Đổi tên .login-ochuathongti thành .register-ochuathongti cho nhất quán */
        .register-ochuathongti {
            max-width: 500px;
            margin: 30px auto; /* Thêm khoảng cách trên dưới */
            padding: 20px 30px; /* Thêm padding */
            background-color: #fff; /* Thêm nền trắng */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .register-ochuathongti h3 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 25px;
            color: #b50202; /* Đổi màu h3 */
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
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        /* (Không dùng .form-options trong HTML này) */
        
        /* Đổi tên .login-button thành .register-button */
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

        /* Link đăng ký/đăng nhập */
        .login-link { /* (Giữ tên class .login-link từ HTML) */
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
        
        /* (Không dùng #account-info trong HTML này) */

        /* CSS Lỗi (từ style inline) */
        .login-error {
            color: #b50202;
            background-color: #ffe8e8;
            border: 1px solid #b50202;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* CSS Quên mật khẩu (từ style inline) */
        .forgot-password-link {
            text-align: right;
            margin-bottom: 15px; /* Khoảng cách với nút đăng nhập */
            margin-top: -10px; /* Gần ô mật khẩu hơn một chút */
        }
        .forgot-password-link a {
            color: #b50202; /* Dùng màu đỏ chủ đạo */
            font-size: 14px;
            text-decoration: none;
        }
        .forgot-password-link a:hover {
            text-decoration: underline;
        }
        
        /* === BẮT ĐẦU RESPONSIVE === */
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
            .register-ochuathongti {
                margin: 15px auto;
                padding: 15px; /* Giảm padding trên mobile */
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
                            <a href="dangnhap.php" style="color: #b50202; font-weight: bold;">Đăng nhập</a> | 
                            <a href="dangky.php">Đăng ký</a>
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
                
                <h3><i class="fas fa-sign-in-alt"></i> Đăng nhập</h3>
                
                <?php 
                if(!empty($login_err)){
                    echo '<div class="login-error">' . htmlspecialchars($login_err) . '</div>';
                }
                ?>

                <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Nhập email của bạn" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                    </div>
                    
                    <div class="forgot-password-link">
                        <a href="quenmatkhau.php">Quên mật khẩu?</a>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="register-button">Đăng nhập</button> 
                    </div>
                    
                    <div class="login-link">
                        <p>Chưa có tài khoản? <a href="dangky.php">Đăng ký ngay</a></p>
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

    </body>
</html>