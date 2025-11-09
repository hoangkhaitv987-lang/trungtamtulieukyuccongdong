<?php
session_start();
$message = '';
$message_type = '';
if (isset($_SESSION['support_message'])) {
    $message = $_SESSION['support_message'];
    $message_type = $_SESSION['support_message_type'] ?? 'error';
    unset($_SESSION['support_message'], $_SESSION['support_message_type']);
}
// Lấy thông tin user nếu đã đăng nhập
$user_fullname = '';
$user_email = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $user_fullname = $_SESSION['fullname'] ?? '';
    
    // Cần truy vấn CSDL để lấy email 
    if (isset($_SESSION['user_id']) || isset($_SESSION['id'])) {
        require_once 'db_connect.php'; // Kết nối CSDL
        $user_id = $_SESSION['user_id'] ?? $_SESSION['id'];
        try {
            $stmt = $pdo->prepare("SELECT email FROM nguoi_dung WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_email = $stmt->fetchColumn();
        } catch (Exception $e) {
        }
    }
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
main h3 { color: #333; }
main h4 { color: #333; }

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

/* CSS BỔ SUNG CHO NỘI DUNG  */
main .content-wrapper {
    background-color: #fff;
    padding: 20px 25px;
    margin-top: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
main .content-wrapper > h3 {
    font-size: 1.8em;
    color: #b50202;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

/* Bố cục 2 cột */
.ho-tro-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
}

.ho-tro-main {
    flex: 3; 
    min-width: 0;
    background-color: #fcfcfc;
    padding: 25px 30px;
    border: 1px solid #eee;
    border-radius: 8px;
}

.ho-tro-sidebar {
    flex: 1;
    min-width: 280px;
}

/* Form liên hệ */
.ho-tro-main h4 {
    font-size: 20px;
    color: #b50202;
    font-weight: bold;
    margin-bottom: 10px;
}

.ho-tro-main p {
    font-size: 15px;
    line-height: 1.7;
    color: #555;
    margin-bottom: 25px;
}

.support-form {
    display: flex;
    flex-direction: column;
}

.support-form .form-group {
    margin-bottom: 18px;
}

.support-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    font-size: 14px;
    color: #333;
}

.support-form input[type="text"],
.support-form input[type="email"],
.support-form select,
.support-form textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box; 
    font-family: Arial, sans-serif;
    font-size: 14px;
}

.support-form select {
    background-color: #fff;
}

.support-form textarea {
    resize: vertical;
    min-height: 120px;
}

.btn-gui-ho-tro {
    background-color: #b50202;
    color: #fff;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
    transition: background-color 0.3s ease;
    width: fit-content; 
}

.btn-gui-ho-tro:hover {
    background-color: #9a0202;
}

/* Sidebar - FAQ */
.sidebar-widget { 
    background-color: #f9f9f9;
    padding: 20px 25px;
    border-radius: 8px;
    border: 1px solid #eee;
    margin-bottom: 25px;
}
.sidebar-widget h3 {
    font-size: 1.3em;
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-top: 0;
    margin-bottom: 20px;
}

.faq-list .faq-item {
    margin-bottom: 18px;
    padding-bottom: 18px;
    border-bottom: 1px dashed #ddd;
}
.faq-list .faq-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.faq-list h5 {
    font-size: 15px;
    font-weight: bold;
    color: #333;
    margin-bottom: 8px;
}

.faq-list p {
    font-size: 14px;
    line-height: 1.6;
    color: #555;
    margin: 0;
}

.faq-list a {
    color: #b50202;
    font-weight: bold;
    text-decoration: underline;
}

/* Sidebar - Contact Info */
.contact-info-widget p {
    font-size: 14px;
    color: #333;
    margin-bottom: 12px;
    line-height: 1.6;
    display: flex;
    align-items: flex-start;
}
.contact-info-widget p i {
    margin-right: 10px;
    color: #b50202;
    width: 15px;
    text-align: center;
    padding-top: 4px; 
}
.contact-info-widget p strong {
    display: block;
    color: #000;
}
/* Banner dưới */
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

/* thông báo  */
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


/* --- RESPONSIVE --- */
@media (max-width: 992px) {
    .ho-tro-wrapper {
        flex-direction: column-reverse; 
    }
    .ho-tro-sidebar {
        min-width: 100%;
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
    
    /* Responsive cho Banner  */
    .lienketngoai {
        grid-template-columns: 1fr;
    }
    
    /* Responsive cho nội dung Hỗ Trợ */
    .ho-tro-main {
        padding: 20px 15px;
    }
    main .content-wrapper {
        padding: 15px;
    }
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
                    <li><a href="gioithieu.php">Giới thiệu</a></li>
                    <li><a href="thongtina50.php">Thông tin A50</a></li>
                    <li><a href="thongtina80.php">Thông tin A80</a></li>
                    <li><a href="congdong.php">Cộng đồng</a></li>
                    <li><a href="hotro.php" style="background-color: #9a0202;">Hỗ trợ</a></li> <li>
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
            
            <h3><i class="fas fa-life-ring"></i> Hỗ trợ & Liên hệ</h3>

            <div class="ho-tro-wrapper">

                <section class="ho-tro-main">
                    <?php if (!empty($message)): ?>
                        <div class="form-message <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    <h4>Gửi yêu cầu hỗ trợ</h4>
                    <p>Nếu bạn có bất kỳ câu hỏi, báo cáo lỗi hoặc đóng góp ý kiến, vui lòng điền vào biểu mẫu bên dưới. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.</p>
                    
                    <form action="xuly_hotro.php" method="post" class="support-form">
                        
                        <div class="form-group">
                            <label for="ho-ten">Họ và tên:</label>
                            <input type="text" id="ho-ten" name="ho-ten" placeholder="Nguyễn Văn A" required 
                                   value="<?php echo htmlspecialchars($user_fullname); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email liên hệ:</label>
                            <input type="email" id="email" name="email" placeholder="bancua@email.com" required 
                                   value="<?php echo htmlspecialchars($user_email); ?>">
                        </div>

                        <div class="form-group">
                            <label for="chu-de">Chủ đề:</label>
                            <select id="chu-de" name="chu-de">
                                <option value="hoi-dap">Hỏi đáp chung</option>
                                <option value="bao-loi">Báo lỗi kỹ thuật</option>
                                <option value="tai-khoan">Vấn đề tài khoản</option>
                                <option value="dong-gop">Đóng góp nội dung/ý kiến</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="noi-dung">Nội dung chi tiết:</label>
                            <textarea id="noi-dung" name="noi-dung" rows="6" placeholder="Vui lòng mô tả rõ vấn đề của bạn..." required></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn-gui-ho-tro">Gửi Yêu Cầu</button>
                        </div>
                    </form>
                </section>

                <aside class="ho-tro-sidebar">
                    
                    <section class="sidebar-widget">
                        <h3><i class="fas fa-question-circle"></i> Câu hỏi thường gặp</h3>
                        <div class="faq-list">
                            <div class="faq-item">
                                <h5>Làm cách nào để đăng bài lên Cộng đồng?</h5>
                                <p>Bạn cần <a href="dangky.php">đăng ký</a> tài khoản và <a href="dangnhap.php">đăng nhập</a>. Sau đó, vào trang <a href="congdong.php">Cộng đồng</a> và nhấn nút "Gửi Ký Ức của bạn" để bắt đầu.</p>
                            </div>
                            <div class="faq-item">
                                <h5>Nội dung của tôi có được duyệt không?</h5>
                                <p>Có, để đảm bảo nội dung phù hợp, tất cả các bài đăng sẽ được ban quản trị xem xét trước khi hiển thị công khai.</p>
                            </div>
                            <div class="faq-item">
                                <h5>Tôi quên mật khẩu, phải làm sao?</h5>
                                <p>Tại trang <a href="dangnhap.php">Đăng nhập</a>, bạn có thể nhấn vào liên kết "Quên mật khẩu" và làm theo hướng dẫn để đặt lại.</p>
                            </div>
                        </div>
                    </section>
                    
                    <section class="sidebar-widget contact-info-widget">
                        <h3><i class="fas fa-address-book"></i> Thông tin liên hệ</h3>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> 73 NGUYỄN HUỆ, PHƯỜNG LONG CHÂU, TỈNH VĨNH LONG</p>
                        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> congthongtina80@gmail.com</p>
                        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> duonghoangkhai.a80@gmail.com</p>
                        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> huynhduchuya.80@gmail.com</p>
                        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> lephuongthuy.a80@gmail.com</p>
                        <p><i class="fas fa-clock"></i> <strong>Hỗ trợ:</strong> 8:00 - 17:00 (Thứ 2 - Thứ 6)</p>
                    </section>

                </aside>
            </div>
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