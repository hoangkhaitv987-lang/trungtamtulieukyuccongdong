<?php
session_start();
require_once 'db_connect.php'; 

$community_images = [];
$article_images = [];

try {
    // 1. Lấy ảnh từ CỘNG ĐỒNG (tag 'a80')
    $sql_community = "SELECT p.duong_dan_media, p.tieu_de
                      FROM bai_dang p
                      JOIN bai_dang_nhan p_n ON p.id = p_n.id_bai_dang
                      JOIN nhan n ON p_n.id_nhan = n.id
                      WHERE p.trang_thai = 'approved' 
                        AND p.loai_media = 'image' 
                        AND (n.slug = 'a80' OR n.ten = 'A80')
                      ORDER BY p.ngay_tao DESC
                      LIMIT 6"; 
    
    $stmt_community = $pdo->query($sql_community);
    $community_images = $stmt_community->fetchAll(PDO::FETCH_ASSOC);

    // 2. Lấy ảnh từ BÀI VIẾT ADMIN (danh mục 'a80')
    $sql_admin = "SELECT bv.duong_dan_media_dai_dien, bv.tieu_de
                  FROM bai_viet bv
                  JOIN danh_muc dm ON bv.id_danh_muc = dm.id
                  WHERE bv.trang_thai = 'published' 
                    AND bv.loai_media = 'image'
                    AND (dm.slug LIKE '%a80%' OR dm.ten LIKE '%A80%')
                  ORDER BY bv.ngay_xuat_ban DESC
                  LIMIT 6"; 
    
    $stmt_admin = $pdo->query($sql_admin);
    $article_images = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Lỗi CSDL, trang vẫn tải
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Thông tin A80 - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
        <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        /* --- CÀI ĐẶT CHUNG --- */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
            
            /* Sticky Footer */
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
        main h3, main h4 { color: #333; } /* h3/h4 */
        ul { list-style: none; margin: 0; padding: 0; }
        img, video { max-width: 100%; height: auto; display: block; } 
        
        /* --- HEADER / NAV / FOOTER CHUNG --- */
        header, footer, main {
            position: relative; 
            z-index: 2;
        }
        main {
            padding: 30px 0; 
            flex-grow: 1;
        }
        footer {
            margin-top: auto;
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
        .footer-info h4 { font-size: 18px; margin-bottom: 10px; color: #fff; }
        .footer-info p { margin: 5px 0; font-size: 14px; }
        .footer-info a { color: #fff; font-weight: bold; }
        .footer-cert img { height: 60px; }
        .footer-bottom { background-color: #b50202; color: #fff; padding: 15px 0; text-align: center; font-size: 13px; }
        .footer-bottom p { margin: 5px 0; }
        
        .content-wrapper {
            background-color: transparent; 
            box-shadow: none; 
            padding: 0; 
        }

        /* --- Card Layout --- */
        .info-card {
            background-color: #fff;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden; 
        }
        .info-card__header {
            padding: 15px 20px;
            background-color: #f8f9fa; 
            border-bottom: 1px solid #e9ecef;
        }
        .info-card__title {
            font-size: 20px;
            color: #b50202;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .info-card__title i {
            margin-right: 10px;
            font-size: 0.9em;
        }
        .info-card__body {
            padding: 20px;
        }

        /* --- 1. Mục Giới thiệu --- */
        .intro-section p {
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 15px;
        }
        .intro-section h4 {
            font-size: 17px;
            color: #333;
            margin-top: 25px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
         .intro-section h4 i {
             margin-right: 8px;
             color: #b50202;
         }
        .intro-section ul {
            list-style-type: none; 
            padding-left: 0;
        }
        .intro-section li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        /* Tạo dấu tick thay thế */
        .intro-section li::before {
            content: "\f00c"; 
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: #28a745; 
            position: absolute;
            left: 0;
            top: 2px;
        }
        .intro-section strong {
            color: #000;
        }

        /* --- 2. Mục Video --- */
        .video-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 20px;
        }
        .video-item video {
            width: 100%;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        .video-caption {
            font-size: 14px;
            color: #555;
            text-align: center;
            margin-top: 8px;
            padding: 0 10px;
        }

        /* --- 3. Mục Thư viện ảnh --- */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 15px;
        }
        .gallery-grid img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .gallery-grid img:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        /* --- 4 & 5. CSS Mục Chia sẻ Ký Ức & Kênh Trực Tuyến --- */
        .community-cta-section { margin-bottom: 25px; background-color: #a54646; border-radius: 8px; padding: 30px; text-align: center; }
        .community-cta-section h3 { color: #fff; border-bottom: none; margin-bottom: 10px; font-size: 22px; display: flex; align-items: center; justify-content: center; }
        .community-cta-section h3 i { margin-right: 10px; }
        .community-cta-section p { font-size: 1.1em; color: #eee; margin-bottom: 20px; }
        .btn-cta-community { display: inline-block; padding: 12px 25px; background-color: #fff; color: #b50202; font-weight: bold; font-size: 1.1em; border-radius: 5px; text-decoration: none; transition: all 0.3s ease; }
        .btn-cta-community:hover { background-color: #f0f0f0; color: #b50202; transform: translateY(-2px); text-decoration: none; }
        
        .channels-section { background-color: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; } 
        .section-title { font-size: 20px; color: #b50202; margin: 0; display: flex; align-items: center; } 
        .section-title i { margin-right: 10px; font-size: 0.9em; }
        .channels-list { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px; } 
        .channels-list a { display: block; border: 1px solid #e9e9e9; border-radius: 5px; padding: 10px; background-color: #fdfdfd; transition: box-shadow 0.2s ease; }
        .channels-list a:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-decoration: none; }
        .channels-list img { width: 100%; object-fit: contain; height: 60px; }

        /* --- LIGHTBOX CSS --- */
        .lightbox-overlay {
            position: fixed; 
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex; 
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
            visibility: hidden; 
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .lightbox-overlay.visible {
            visibility: visible;
            opacity: 1;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 90vh;
            object-fit: contain;
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }
        .lightbox-overlay.visible .lightbox-content {
            transform: scale(1);
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
            z-index: 1002;
        }
        .lightbox-close:hover {
            color: #bbb;
        }
        .lightbox-prev, .lightbox-next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            margin-top: -22px;
            color: white;
            font-weight: bold;
            font-size: 30px;
            transition: 0.3s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
            z-index: 1001;
        }
        .lightbox-prev {
            left: 10px;
            border-radius: 3px 0 0 3px;
        }
        .lightbox-next {
            right: 10px;
        }
        .lightbox-prev:hover, .lightbox-next:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }


        /* --- RESPONSIVE --- */
        @media (max-width: 992px) {
            .nav-bar .ochuathongti ul { flex-wrap: wrap; }
        }
        @media (max-width: 768px) {
            /* Bổ sung Header/Nav/Footer */
            .header-top .ochuathongti, .footer-top .ochuathongti { 
                flex-direction: column; 
                text-align: center; 
                gap: 15px;
            }
            .logo { flex-direction: column; }
            .logo-img { margin-right: 0; margin-bottom: 10px; }
            .logo-text h1 { font-size: 1.2em; line-height: 1.3; }
            .header-phai { margin-top: 15px; flex-direction: column; gap: 10px; }
            
            .nav-bar .ochuathongti ul { flex-direction: column; }
            .nav-bar .ochuathongti ul li a { text-align: center; padding: 12px 10px; }
            .timkiem-box { width: 90%; margin: 5px auto; }
            .nav-bar .ochuathongti ul li a.timkiemnangcao {
                background-color: #9a0202;
                margin: 5px auto;
                width: 90%;
                box-sizing: border-box;
                border-radius: 5px;
            }
            .footer-top .ochuathongti > * { margin-bottom: 15px; }
            .video-grid {
                grid-template-columns: 1fr;
            }
            .gallery-grid {
                 grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); 
                 gap: 10px;
            }
             .gallery-grid img {
                 height: 100px;
             }
            .channels-list {
                 grid-template-columns: 1fr; 
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
                    <li><a href="thongtina80.php" style="background-color: #9a0202;">Thông tin A80</a></li>
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
            
            <section class="info-card intro-section">
                <header class="info-card__header">
                    <h3 class="info-card__title"><i class="fas fa-info-circle"></i> Giới thiệu về Lễ diễu binh A80</h3>
                </header>
                <div class="info-card__body">
                    <p>Lễ diễu binh, diễu hành A80 là sự kiện trọng đại, đánh dấu một mốc son lịch sử hào hùng của dân tộc. Đây là dịp để toàn Đảng, toàn dân và toàn quân ta ôn lại truyền thống vẻ vang, biểu dương lực lượng và khẳng định ý chí quyết tâm bảo vệ vững chắc Tổ quốc.</p>
                    <p>Sự kiện quy tụ hàng ngàn cán bộ, chiến sĩ từ các quân binh chủng, các lực lượng vũ trang nhân dân, cùng các khối đại diện cho các tầng lớp nhân dân, thể hiện sức mạnh đại đoàn kết toàn dân tộc.</p>
                    
                    <h4><i class="fas fa-calendar-alt"></i> Thông tin chính về sự kiện</h4>
                    <ul>
                        <li><strong>Thời gian:</strong> 7:00 sáng, ngày 02/09/2025</li>
                        <li><strong>Địa điểm:</strong> Quảng trường Ba Đình, Hà Nội và các tuyến phố lân cận.</li>
                        <li><strong>Quy mô:</strong> Cấp Quốc gia đặc biệt, với sự tham gia của nhiều khối diễu binh, diễu hành và trình diễn khí tài hiện đại.</li>
                    </ul>
                </div>
            </section>

            <section class="info-card video-section">
                 <header class="info-card__header">
                    <h3 class="info-card__title"><i class="fas fa-video"></i> Video nổi bật</h3>
                 </header>
                 <div class="info-card__body">
                    <div class="video-grid">
                        <div class="video-item">
                            <video controls preload="metadata">
                                <source src="khovideo/a80/HIGHLIGHT- NHỮNG KHOẢNH KHẮC ẤN TƯỢNG TRONG LỄ DIỄU BINH, DIỄU HÀNH A80 - VTV24.mp4" type="video/mp4">
                            </video>
                            <p class="video-caption">HIGHLIGHT: Khoảnh khắc ấn tượng trong Lễ diễu binh A80</p>
                        </div>
                        <div class="video-item">
                            <video controls preload="metadata">
                                <source src="khovideo/a80/khiphachvietnam.mp4" type="video/mp4">
                            </video>
                            <p class="video-caption">Nhìn lại hình ảnh từ tập luyện đến diễu binh chính thức</p>
                        </div>
                    </div>
                 </div>
            </section>

            <section class="info-card gallery-section">
                 <header class="info-card__header">
                    <h3 class="info-card__title"><i class="fas fa-images"></i> Thư viện hình ảnh A80</h3>
                 </header>
                 <div class="info-card__body">
                    <div class="gallery-grid">
                        <img src="khoanh/a80/img0631-17567967413481704453104.jpg" alt="Hình ảnh A80 1">
                        <img src="khoanh/a80/img0691-17567975101461917352448.jpg" alt="Hình ảnh A80 2">
                        <img src="khoanh/a80/jum00459-1756786775947316074236.jpg" alt="Hình ảnh A80 3">
                        <img src="khoanh/a80/dsc3682-17567844237391034274362.jpg" alt="Hình ảnh A80 4">
                        <img src="khoanh/a80/tausanngam-ludoan171vaovitri-1756763083794593338590.jpg" alt="Hình ảnh A80 5">
                        <img src="khoanh/a80/edit-tau-chi-huy-di-giua-dang-duyet-doi-hinh-dieu-binh-tren-bien-17567795986431209891294.png" alt="Hình ảnh A80 6">

                        <?php foreach ($community_images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image['duong_dan_media']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['tieu_de']); ?>">
                        <?php endforeach; ?>

                        <?php foreach ($article_images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image['duong_dan_media_dai_dien']); ?>" 
                                 alt="<?php echo htmlspecialchars($image['tieu_de']); ?>">
                        <?php endforeach; ?>
                        
                    </div>
                 </div>
            </section>

            <section class="community-cta-section">
                <h3><i class="fas fa-users"></i> CHIA SẺ KÝ ỨC A80</h3>
                <p>Tham gia cộng đồng để chia sẻ câu chuyện, hình ảnh và cảm xúc của bạn!</p>
                <a href="congdong.php" class="btn-cta-community">
                    <i class="fas fa-paper-plane"></i> Xem & Gửi bài viết
                </a>
            </section>      
            
            <section class="info-card channels-section">
                 <header class="info-card__header">
                    <h3 class="info-card__title section-title"><i class="fas fa-tv"></i> Kênh trực tuyến</h3>
                 </header>
                 <div class="info-card__body">
                    <div class="channels-list">
                        <a href="https://vtvgo.vn/" target="_blank" rel="noopener noreferrer">
                            <img src="image/vtvctn.png" alt="VTVGO">
                        </a>
                        <a href="https://www.thvli.vn/" target="_blank" rel="noopener noreferrer">
                            <img src="image/logo thvl.png" alt="THVL">
                        </a>
                    </div>
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

    <div id="lightbox-modal" class="lightbox-overlay">
        <span class="lightbox-close">&times;</span>
        <a class="lightbox-prev">&#10094;</a>
        <a class="lightbox-next">&#10095;</a>
        <img class="lightbox-content" id="lightbox-image">
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("lightbox-modal");
        const modalImg = document.getElementById("lightbox-image");
        const closeBtn = document.querySelector(".lightbox-close");
        const prevBtn = document.querySelector(".lightbox-prev");
        const nextBtn = document.querySelector(".lightbox-next");

        // 1. Thu thập tất cả nguồn ảnh từ csdl
        const galleryImages = document.querySelectorAll(".gallery-grid img");
        const imageSources = [];
        galleryImages.forEach(img => {
            imageSources.push(img.src);
        });

        let currentImageIndex = 0;

        // 2. để mở modal
        function openModal(index) {
            if (imageSources.length === 0) return; 
            if (index < 0) {
                index = imageSources.length - 1; 
            } else if (index >= imageSources.length) {
                index = 0; 
            }
            
            currentImageIndex = index;
            modalImg.src = imageSources[currentImageIndex];
            modal.classList.add("visible");
        }

        // 3. Hàm đóng modal
        function closeModal() {
            modal.classList.remove("visible");
        }

        // 4. lick cho từng ảnh trong grid
        galleryImages.forEach((img, index) => {
            img.addEventListener("click", function() {
                openModal(index);
            });
        });

        // 5. Thêm nút Đóng, Trước, Sau
        closeBtn.addEventListener("click", closeModal);
        prevBtn.addEventListener("click", () => openModal(currentImageIndex - 1));
        nextBtn.addEventListener("click", () => openModal(currentImageIndex + 1));

        // 6. Đóng khi bấm vào nền đen
        modal.addEventListener("click", function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // 7. n
        // đk bằng bàn phím
        document.addEventListener("keydown", function(e) {
            if (modal.classList.contains("visible")) {
                if (e.key === "ArrowLeft") {
                    openModal(currentImageIndex - 1);
                } else if (e.key === "ArrowRight") {
                    openModal(currentImageIndex + 1);
                } else if (e.key === "Escape") {
                    closeModal();
                }
            }
        });
    });
    </script>
    </body>
</html>