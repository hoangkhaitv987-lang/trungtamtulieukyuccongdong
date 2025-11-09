<?php
session_start();
require_once 'db_connect.php';

// ---LẤY 5 BÀI ĐĂNG MỚI NHẤT ĐÃ DUYỆT ---
$latest_posts = [];
try {
    $sql_latest = "SELECT id, tieu_de AS title FROM bai_dang WHERE trang_thai = 'approved' ORDER BY ngay_tao DESC LIMIT 5";
    $latest_posts = $pdo->query($sql_latest)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $latest_posts = []; 
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
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
        .timkiem-box button { padding: 0 20px; font-size: 18px; border: none; background-color: #b50202; color: #fff; cursor: pointer; border-radius: 05px 5px 0; }
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
        
        /* SLIDE */
        .slider-container {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            /*border: 5px solid rgb(255, 255, 255);*/
        }
        .slider { width: 1000%; display: flex; animation: slide-animation 40s linear infinite; }
        .slide { width: 10%; height: 450px; background-size: cover; background-position: center; }
        .slide-1 { background-image: url('khoanh/slide/_DMH8224.jpg'); }
        .slide-2 { background-image: url('khoanh/slide/edit-doi-hinh-chu-v-1756777817032270494903.png'); }
        .slide-3 { background-image: url('khoanh/slide/_DMH8293.jpg'); }
        .slide-4 { background-image: url('khoanh/slide/img0631-17567967413481704453104.jpg'); }
        .slide-5 { background-image: url('khoanh/slide/img0710-17567975447592095068139.jpg'); }
        .slide-6 { background-image: url('khoanh/slide/_DMH8324.jpg'); }
        .slide-7 { background-image: url('khoanh/slide/img0686-17567974211361482915515.jpg'); }
        .slide-8 { background-image: url('khoanh/slide/img0691-17567975101461917352448.jpg'); }
        .slide-9 { background-image: url('khoanh/slide/tu-17565257699161155433046.jpg'); }
        @keyframes slide-animation {
            0%   { transform: translateX(0%); } 9%   { transform: translateX(0%); }
            11.1% { transform: translateX(-10%); } 20.1% { transform: translateX(-10%); }
            22.2% { transform: translateX(-20%); } 31.2% { transform: translateX(-20%); }
            33.3% { transform: translateX(-30%); } 42.3% { transform: translateX(-30%); }
            44.4% { transform: translateX(-40%); } 53.4% { transform: translateX(-40%); }
            55.5% { transform: translateX(-50%); } 64.5% { transform: translateX(-50%); }
            66.6% { transform: translateX(-60%); } 75.6% { transform: translateX(-60%); }
            77.7% { transform: translateX(-70%); } 86.7% { transform: translateX(-70%); }
            88.8% { transform: translateX(-80%); } 97.8% { transform: translateX(-80%); }
            100% { transform: translateX(-90%); }
        }
        
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
        main { padding: 20px 0; }
        .content-wrapper {
            background-color: #ffffff; 
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px; 
        }

        /*Tiêu đề mục chung */
        .section-title {
            font-size: 22px;
            color: #b50202;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        .section-title i {
            margin-right: 10px;
            font-size: 0.9em;
        }

        /*Mục Tin Mới*/
        .latest-posts-section {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #f0f0f0; 
        }
        .latest-posts-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .latest-posts-list li {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }
        .latest-posts-list li:last-child {
            border-bottom: none;
        }
        .latest-posts-list a {
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            color: #333;
            transition: color 0.2s;
        }
        .latest-posts-list a:hover {
            color: #b50202;
        }

        /* A50 / A80   */
        .info-grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 20px;
            margin-bottom: 25px;
        }

        /* Thẻ Card cho A50 / A80 */
        .info-card {
            background-color: #fff;
            border: 1px solid #e9e9e9;
            border-radius: 8px;
            overflow: hidden; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s ease;
        }
        .info-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .info-card__header {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-card__header a {
            font-size: 18px;
            font-weight: bold;
            color: #b50202;
            text-decoration: none;
        }
        .info-card__header a i {
            margin-right: 8px;
        }
        .info-card__content {
            padding: 15px;
        }
        .info-card__content video {
            width: 100%;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .info-card__gallery {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-card__gallery img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            border-radius: 5px;
            transition: transform 0.2s ease, opacity 0.2s; 
        }
        .info-card__gallery img:hover {
            transform: scale(1.05);
            cursor: pointer;
            opacity: 0.8;
        }
        
        /* Mục Chia sẻ Ký Ức*/
        .community-cta-section {
            margin-bottom: 25px;
            background-color: #a54646; 
            border-radius: 8px;
            padding: 30px;
            text-align: center;
        }
        .community-cta-section h3 {
             color: #fff;
             border-bottom: none;
             margin-bottom: 10px;
             font-size: 22px;
        }
        .community-cta-section p {
            font-size: 1.1em;
            color: #eee;
            margin-bottom: 20px;
        }
        .btn-cta-community {
            display: inline-block;
            padding: 12px 25px;
            background-color: #fff;
            color: #b50202;
            font-weight: bold;
            font-size: 1.1em;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-cta-community:hover {
            background-color: #f0f0f0;
            color: #b50202;
            transform: translateY(-2px);
            text-decoration: none; 
        }

        /* kênh Trực Tuyến   */
        .channels-section {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }
        .channels-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .channels-list a {
            display: block;
            border: 1px solid #e9e9e9;
            border-radius: 5px;
            padding: 10px;
            background-color: #fff;
            transition: box-shadow 0.2s ease;
        }
        .channels-list a:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-decoration: none;
        }
        .channels-list img {
            width: 100%;
            object-fit: contain;
            height: 60px; 
        }
        
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

        .lightbox-overlay {
            display: none; 
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .lightbox-content {
            max-width: 90%;
            max-height: 90vh;
            object-fit: contain;
            animation: zoomIn 0.3s ease;
        }
        .lightbox-close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .lightbox-close:hover {
            color: #bbb;
        }
        @keyframes zoomIn {
            from {transform: scale(0.8);}
            to {transform: scale(1);}
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 992px) {
            .nav-bar .ochuathongti ul { flex-wrap: wrap; }
            .logo-text h1 { font-size: 20px; }
            .slide { height: 350px; } 
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

            /* Slider responsive */
            .slider-container { width: 95%; margin: 15px auto; border-width: 3px; }
            .slide { height: 250px; } 

            /* Content responsive */
            .content-wrapper { padding: 15px; }
            .info-grid-container {
                grid-template-columns: 1fr; 
            }
            .channels-list {
                grid-template-columns: 1fr; 
            }
            .info-card__gallery img {
                height: 140px;
            }
            .latest-posts-list {
                 max-height: none; 
                 overflow: visible;
            }
            .section-title { font-size: 1.3em; }
            .community-cta-section { padding: 20px; }
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
                    <li><a href="trangchu.php" style="background-color: #9a0202;"><i class="fas fa-home"></i></a></li> <li><a href="gioithieu.php">Giới thiệu</a></li>
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
    <div class="br">     
    <div class="slider-container">      
        <div class="slider">            
            <div class="slide slide-1"></div>
            <div class="slide slide-2"></div>
            <div class="slide slide-3"></div>
            <div class="slide slide-4"></div>
            <div class="slide slide-5"></div>
            <div class="slide slide-6"></div>
            <div class="slide slide-7"></div>
            <div class="slide slide-8"></div>
            <div class="slide slide-9"></div>
            <div class="slide slide-1"></div>
        </div>
    </div>
    </div>
    
    <main>
        <div class="ochuathongti content-wrapper">
            <section class="latest-posts-section">
                <h3 class="section-title"><i class="fas fa-bullhorn"></i> Thông tin mới cập nhật</h3>
                <ul class="latest-posts-list">
                    <?php if (empty($latest_posts)): ?>
                        <li><a href="congdong.php">Chào mừng! Hãy là người đầu tiên đăng bài.</a></li>
                    <?php else: ?>
                        <?php foreach ($latest_posts as $post): ?>
                            <li>
                                <a href="congdong.php?id=<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>          
            <div class="info-grid-container">
                <article class="info-card">
                    <header class="info-card__header">
                        <a href="thongtina50.php">
                            <i class="fas fa-film"></i>
                            THÔNG TIN VỀ A50
                        </a>
                    </header>
                    <div class="info-card__content">
                        <video controls preload="metadata">
                           <source src="khovideo/a50/Việt Nam tôi đó! - Tổng hợp Lễ diễu binh, diễu hành Kỷ niệm 50 năm Ngày Thống nhất đất nước - VTV24.mp4" type="video/mp4">
                        </video>
                        <div class="info-card__gallery">
                            <img src="khoanh/a50/_DMH8539.jpg" alt="Hình ảnh A50 1">
                            <img src="khoanh/a50/_DMH8452.jpg" alt="Hình ảnh A50 2">
                            <img src="khoanh/a50/_DMH8356.jpg" alt="Hình ảnh A50 3">
                            <img src="khoanh/a50/_DMH8341.jpg" alt="Hình ảnh A50 4">
                        </div>
                    </div>
                </article>
                <article class="info-card">
                    <header class="info-card__header">
                        <a href="thongtina80.php">
                            <i class="fas fa-star"></i>
                            THÔNG TIN VỀ A80
                        </a>
                    </header>
                    <div class="info-card__content">
                        <video controls preload="metadata">
                           <source src="khovideo/a80/khiphachvietnam.mp4" type="video/mp4">
                        </video>
                        <div class="info-card__gallery">
                            <img src="khoanh/a80/img0691-17567975101461917352448.jpg" alt="Hình ảnh A80 1">
                            <img src="khoanh/a80/img0687-1756797372425810665796.jpg" alt="Hình ảnh A80 2">
                            <img src="khoanh/a80/can-canh-chapf-17567615252291183580750.jpg" alt="Hình ảnh A80 3">
                            <img src="khoanh/a80/img0691-17567975101461917352448.jpg" alt="Hình ảnh A80 4">
                        </div>
                    </div>
                </article>

            </div> <section class="community-cta-section">
                <h3><i class="fas fa-users"></i> CHIA SẺ KÝ ỨC A80</h3>
                <p>Tham gia cộng đồng để chia sẻ câu chuyện, hình ảnh và cảm xúc của bạn!</p>
                <a href="congdong.php" class="btn-cta-community">
                    <i class="fas fa-paper-plane"></i> Xem & Gửi bài viết
                </a>
            </section>
            
            
            <section class="channels-section">
                <h3 class="section-title"><i class="fas fa-tv"></i> Kênh trực tuyến</h3>
                <div class="channels-list">
                    <a href="https://vtvgo.vn/" target="_blank" rel="noopener noreferrer">
                        <img src="image/vtvctn.png" alt="VTVGO">
                    </a>
                    <a href="https://www.thvli.vn/" target="_blank" rel="noopener noreferrer">
                        <img src="image/logo thvl.png" alt="THVL">
                    </a>
                </div>
            </section>
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

    <div id="lightbox-modal" class="lightbox-overlay">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-image">
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy các phần tử modal
        const modal = document.getElementById("lightbox-modal");
        const modalImg = document.getElementById("lightbox-image");
        const closeBtn = document.querySelector(".lightbox-close");

        // Lấy tất cả ảnh trong thư viện
        const galleryImages = document.querySelectorAll(".info-card__gallery img");

        // Lặp qua từng ảnh và thêm click
        galleryImages.forEach(img => {
            img.addEventListener("click", function() {
                modal.style.display = "flex"; 
                modalImg.src = this.src;     
            });
        });

        // Hàm đóng modal
        function closeModal() {
            modal.style.display = "none";
        }

        // Bấm vào nút (X) để đóng
        closeBtn.addEventListener("click", closeModal);

        // Bấm vào nền mờ để đóng
        modal.addEventListener("click", function(e) {
            // Chỉ đóng nếu bấm vào nền 
            if (e.target === modal) {
                closeModal();
            }
        });
    });
    </script>
    </body>
</html>