<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: dangnhap.php");
    exit;
}
$ten_nguoi_dung = $_SESSION['fullname'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng bạn - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
        <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /*CÀI ĐẶT CHUNG  */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5; 
            color: #333;
            line-height: 1.6;
            
            /* Sticky footer */
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
        a { color: #333; text-decoration: none; }
        a:hover { color: #b50202; text-decoration: underline; }
        h1, h2, h3, h4 { margin: 0; padding: 0; }
        ul { list-style: none; margin: 0; padding: 0; }
        img { max-width: 100%; height: auto; }

        /* --- CSS HEADER, NAV, FOOTER --- */
        header {
            position: relative; /* Đảm bảo z-index */
            z-index: 2;
        }
        footer {
            position: relative; /* Đảm bảo z-index */
            z-index: 2;
            margin-top: auto; /* Sticky footer */
        }
        main {
            padding: 40px 0; 
            flex-grow: 1; /* Sticky footer */
            position: relative; /* Đảm bảo z-index */
            z-index: 2;
        } 
        
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
        
        .footer-top { background-color: #380000; color: #fff; padding: 20px 0; }
        .footer-top .ochuathongti { display: flex; justify-content: space-between; align-items: center; }
        .footer-logo { width: 80px; height: 80px; }
        .footer-info { flex-grow: 1; margin: 0 20px; }
        .footer-info h4 { font-size: 18px; margin-bottom: 10px; color: #fff; } /* Thêm color */
        .footer-info p { margin: 5px 0; font-size: 14px; }
        .footer-info a { color: #fff; font-weight: bold; }
        .footer-cert img { height: 60px; }
        .footer-bottom { background-color: #b50202; color: #fff; padding: 15px 0; text-align: center; font-size: 13px; }
        .footer-bottom p { margin: 5px 0; }
        
        /* CSS Trang Chào Mừng */
        .content-wrapper {
             background-color: transparent; 
             box-shadow: none; 
             padding: 0; 
        }

        .info-card {
            background-color: #fff;
            border-radius: 8px;
            margin: 0 auto;
            max-width: 700px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center; 
        }
        .info-card__header {
            padding: 25px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .info-card__title {
            font-size: 2em; 
            color: #b50202;
            margin: 0;
            display: flex; 
            align-items: center;
            justify-content: center;
        }
         .info-card__title i {
             margin-right: 12px;
             font-size: 0.9em;
         }
        .info-card__body {
            padding: 30px 25px;
        }

    
         .welcome-message {
             font-size: 1.15em;
             color: #444;
             margin-bottom: 30px;
         }
         .welcome-message strong {
             color: #b50202;
         }


        .welcome-actions {
            display: flex;
            justify-content: center;
            gap: 15px; 
            flex-wrap: wrap; 
        }
        
        .welcome-actions a {
            text-decoration: none;
            color: #fff;
            background-color: #b50202; 
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 1.05em; 
            display: inline-flex;
            align-items: center;
            gap: 8px; 
        }
        .welcome-actions a:hover {
            background-color: #9a0202;
            transform: translateY(-2px); 
            color: #fff;
            text-decoration: none;
        }
        .welcome-actions a.btn-secondary { 
            background-color: #6c757d; 
        }
        .welcome-actions a.btn-secondary:hover {
            background-color: #5a6268;
        }
        
        /* === BẮT ĐẦU RESPONSIVE === */
         @media (max-width: 768px) {
            /* Responsive cho Header */
            .header-top .ochuathongti {
                flex-direction: column;
                gap: 15px;
                text-align: center;
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
         
            /* Responsive nội dung Chào mừng */
            main { padding: 20px 0; }
            .info-card__title { font-size: 1.8em; }
            .welcome-message { font-size: 1.1em; }
            .welcome-actions a { padding: 10px 20px; font-size: 1em; }
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
                        <span>Chào, <?php echo htmlspecialchars($ten_nguoi_dung); ?>!</span> | 
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
                    <li><a href="taikhoan.php" class="timkiemnangcao">Thông tin tài khoản</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="ochuathongti content-wrapper">
            <div class="info-card welcome-card">
                 <header class="info-card__header">
                       <h3 class="info-card__title">
                           <i class="fas fa-check-circle"></i> Đăng nhập thành công!
                       </h3>
                 </header>
                 <div class="info-card__body">
                       <p class="welcome-message">
                           Chào mừng <strong><?php echo htmlspecialchars($ten_nguoi_dung); ?></strong> đã quay trở lại CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG.
                       </p>
                       <div class="welcome-actions">
                           <a href="trangchu.php">
                               <i class="fas fa-home"></i> Về Trang chủ
                           </a>
                           <a href="taikhoan.php">
                               <i class="fas fa-user-circle"></i> Xem Tài khoản
                           </a>
                           <a href="logout.php" class="btn-secondary"> <i class="fas fa-sign-out-alt"></i> Đăng xuất
                           </a>
                       </div>
                 </div>
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