<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
        <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
    <style>
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

/* Thêm content-wrapper cho main */
main .content-wrapper {
    background-color: #fff;
    padding: 20px;
    margin-top: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Phần giới thiệu chung */
.gioi-thieu-chung {
    margin-bottom: 30px;
    padding: 20px;
    background-color: #fcfcfc;
    border: 1px solid #eee;
    border-radius: 8px;
}
.gioi-thieu-chung h3 {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.gioi-thieu-chung p {
    font-size: 16px;
    line-height: 1.8;
    color: #444;
    margin-bottom: 15px;
}

.gioi-thieu-chung p:last-child {
    margin-bottom: 0;
}

/* Phần giới thiệu A50, A80 */
.gioi-thieu-su-kien {
    margin-bottom: 30px;
}
.gioi-thieu-su-kien h3 {
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

/* Bố cục 2 cột cho 2 thẻ */
.su-kien-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr; 
    gap: 25px;
}

/* Định dạng cho từng thẻ */
.su-kien-card {
    background-color: #fff;
    border: 1px solid #eee;
    border-radius: 8px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.su-kien-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.su-kien-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
}

.su-kien-content {
    padding: 25px;
    display: flex;
    flex-direction: column;
    flex-grow: 1; 
}

.su-kien-content h4 {
    font-size: 20px;
    color: #b50202;
    font-weight: bold;
    margin-bottom: 15px;
}

.su-kien-content p {
    font-size: 15px;
    line-height: 1.7;
    color: #555;
    margin-bottom: 20px;
    flex-grow: 1; 
}

/* Nút bấm nhỏ "Xem chi tiết" */
.cta-button-small {
    display: inline-block;
    background-color: #f1f1f1;
    color: #333;
    padding: 10px 18px;
    border-radius: 5px;
    font-weight: bold;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #ddd;
    text-align: center;
    width: fit-content; 
}

.cta-button-small:hover {
    background-color: #e5e5e5;
    color: #000;
    text-decoration: none;
    border-color: #ccc;
}

/* Nút bấm lớn  */
.cta-button {
    display: inline-block;
    background-color: #b50202;
    color: #fff;
    padding: 12px 25px;
    font-size: 1.1em;
    font-weight: bold;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}
.cta-button:hover {
    background-color: #9a0202;
    color: #fff;
    text-decoration: none;
}
.cta-button i {
    margin-right: 8px;
}


/* Phần kêu gọi hành động cuối trang */
.call-to-action-gioithieu {
    text-align: center;
    background-color: #f9f9f9;
    padding: 30px 25px;
    border-radius: 8px;
    margin-top: 25px;
    border: 1px dashed #ddd;
}

.call-to-action-gioithieu h3 {
    border-bottom: none; 
    margin-bottom: 10px;
    font-size: 22px;
}

.call-to-action-gioithieu p {
    font-size: 16px;
    color: #555;
    margin-bottom: 25px;
}

/* Banner dưới  */
.lienketngoai {
    margin-top: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.lienketngoai img {
    width: 100%;
    border: 1px solid #eee;
    border-radius: 5px;
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
    color: #fff; 
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
@media (max-width: 992px) {

    
    .nav-bar .ochuathongti ul {
        flex-wrap: wrap;
        justify-content: center;
    }
}

/* Responsive */
@media (max-width: 768px) {
    /* 1 cột cho 2 thẻ sự kiện */
    .su-kien-wrapper {
        grid-template-columns: 1fr;
    }
    
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
    
    /* Responsive  */
    .lienketngoai {
        grid-template-columns: 1fr;
    }
}
/* br hinh anh thay doi */
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

    </style>
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
                    <li><a href="gioithieu.php" style="background-color: #9a0202;">Giới thiệu</a></li>
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

            <section class="gioi-thieu-chung">
                <h3><i class="fas fa-globe-asia"></i> Về cổng thông tin của chúng tôi</h3>
                <p>Chào mừng bạn đến với <strong>Cổng thông tin TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</strong>. Trang web này được xây dựng với mục tiêu trở thành một kho lưu trữ kỹ thuật số, một không gian cộng đồng để lưu giữ và chia sẻ những khoảnh khắc, ký ức và câu chuyện hào hùng liên quan đến các sự kiện lịch sử trọng đại của đất nước.</p>
                <p>Nơi đây không chỉ cung cấp thông tin chính thức, hình ảnh, video tư liệu về Lễ A50 và A80 mà còn là một diễn đàn mở để mỗi người dân Việt Nam, dù ở bất cứ đâu, cũng có thể <strong>"Gửi Ký Ức"</strong> của mình. Chúng tôi tin rằng mỗi câu chuyện cá nhân, mỗi tấm ảnh gia đình đều là một mảnh ghép vô giá, góp phần xây dựng một bức tranh toàn cảnh và sống động về niềm tự hào dân tộc.</p>
            </section>

            <section class="gioi-thieu-su-kien">
                <h3><i class="fas fa-history"></i> Các sự kiện lịch sử nổi bật</h3>
                
                <div class="su-kien-wrapper">
                    
                    <article class="su-kien-card">
                        <img src="khoanh/slide/_DMH8224.jpg" alt="Hình ảnh A50" class="su-kien-img">
                        <div class="su-kien-content">
                            <h4>Lễ Kỷ niệm A50 (30/04/2025)</h4>
                            <p>Đánh dấu 50 năm ngày Giải phóng miền Nam, thống nhất đất nước. Lễ diễu binh, diễu hành A50 là dịp để ôn lại truyền thống hào hùng, biểu dương lực lượng và thành tựu của đất nước sau nửa thế kỷ xây dựng và phát triển.</p>
                            <a href="thongtina50.php" class="cta-button-small">Xem chi tiết A50</a>
                        </div>
                    </article>
                    
                    <article class="su-kien-card">
                        <img src="khoanh/a80/hinhen.jpg" alt="Hình ảnh A80" class="su-kien-img">
                        <div class="su-kien-content">
                            <h4>Lễ Kỷ niệm A80 (02/09/2025)</h4>
                            <p>Kỷ niệm 80 năm ngày Quốc khánh nước Cộng hòa Xã hội Chủ nghĩa Việt Nam. Sự kiện A80 là một mốc son chói lọi, khẳng định vị thế, ý chí kiên cường và sức mạnh đại đoàn kết toàn dân tộc trong thời đại mới.</p>
                            <a href="thongtina80.php" class="cta-button-small">Xem chi tiết A80</a>
                        </div>
                    </article>

                </div>
            </section>

            <section class="call-to-action-gioithieu">
                <h3>Trở thành một phần của lịch sử</h3>
                <p>Đừng để những ký ức quý giá của bạn bị lãng quên. Hãy chia sẻ câu chuyện của bạn và truyền cảm hứng cho thế hệ tương lai.</p>
                <a href="congdong.php" class="cta-button">
                    <i class="fas fa-pen-fancy"></i> Tới Trang Cộng Đồng
                </a>
            </section>

        </div>
    </main>
    <footer>
        <div class="footer-top">
            <div class="ochuathongti">
                <img src="image/AVATAR.png" alt="Logo" class="footer-logo">
                <div class="footer-info">
                    <h4>CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</h4>
                    <p>Địa chỉ: 73 NGUYỄN HUỆ, PHƯỜN LONG CHÂU, TỈNH VĨNH LONG</p>
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