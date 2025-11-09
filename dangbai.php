<?php

session_start();
require_once 'db_connect.php'; 

// --- KIỂM TRA ĐĂNG NHẬP ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: dangnhap.php?error=banphaidangnhap");
    exit;
}

// Lấy user_id (hỗ trợ cả hai kiểu session)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} else {
    // Nếu không tìm thấy ID, bắt buộc đăng nhập lại
    header("location: dangnhap.php?error=banphaidangnhap");
    exit;
}

$message = '';
$message_type = '';

// --- BƯỚC 2: XỬ LÝ FORM KHI NGƯỜI DÙNG GỬI (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Lấy dữ liệu
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    $tags_input = trim($_POST['tags']);
    
    $media_path = '';
    $media_type = 'image'; 

    // --- BƯỚC 3: XỬ LÝ FILE UPLOAD ---
    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
        $upload_dir = 'uploads/'; 
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = time() . '_' . basename($_FILES['media_file']['name']);
        $target_path = $upload_dir . $filename;
        
        $fileType = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
        
        $allowed_images = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_videos = ['mp4', 'mov', 'avi', 'wmv']; 

        if (in_array($fileType, $allowed_images)) {
            $media_type = 'image';
        } elseif (in_array($fileType, $allowed_videos)) {
            $media_type = 'video';
        } else {
            $message = "File không hợp lệ. Chỉ chấp nhận ảnh (JPG, PNG, GIF) hoặc video (MP4, MOV, AVI).";
            $message_type = 'error';
        }
        

        if (empty($message) && $_FILES['media_file']['size'] > 100 * 1024 * 1024) { 
             $message = "Lỗi: File quá lớn. Giới hạn là 100MB.";
             $message_type = 'error';
        }
        
        if (empty($message)) {
            if (move_uploaded_file($_FILES['media_file']['tmp_name'], $target_path)) {
                $media_path = $target_path;
            } else {
                $message = "Đã xảy ra lỗi khi tải file lên.";
                $message_type = 'error';
            }
        }
    } else {
        $message = "Vui lòng chọn một hình ảnh hoặc video.";
        $message_type = 'error';
    }

    // --- BƯỚC 4: LƯU VÀO CSDL (NẾU KHÔNG CÓ LỖI) ---
    if (!empty($media_path) && empty($message)) {

        // Bắt đầu Transaction
        $pdo->beginTransaction();
        try {
            // 1. Thêm vào bảng 'bai_dang'
            $insert_sql = "INSERT INTO bai_dang (id_nguoi_dung, tieu_de, noi_dung, duong_dan_media, loai_media, trang_thai, ngay_tao) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $pdo->prepare($insert_sql);
            $stmt->execute([$user_id, $title, $content, $media_path, $media_type]);
            
            // Lấy ID của bài viết vừa mới chèn
            $post_id = $pdo->lastInsertId();

            // 2. === BỔ SUNG: XỬ LÝ TAGS ===
            if (!empty($tags_input) && $post_id > 0) {
                // Chuẩn bị các câu lệnh SQL
                $sql_find_tag = "SELECT id FROM nhan WHERE slug = ?";
                $stmt_find = $pdo->prepare($sql_find_tag);
                
                $sql_insert_tag = "INSERT INTO nhan (ten, slug) VALUES (?, ?)";
                $stmt_insert_tag = $pdo->prepare($sql_insert_tag);
                
                $sql_link = "INSERT IGNORE INTO bai_dang_nhan (id_bai_dang, id_nhan) VALUES (?, ?)";
                $stmt_link = $pdo->prepare($sql_link);

                // Tách các thẻ bằng dấu phẩy hoặc khoảng trắng
                $tag_names = preg_split('/[,\s]+/', $tags_input, -1, PREG_SPLIT_NO_EMPTY);
                
                foreach ($tag_names as $tag_name) {
                    $tag_name_clean = trim($tag_name, '#'); // Bỏ dấu #
                    if (empty($tag_name_clean)) continue;

                    // Tạo slug đơn giản
                    $tag_slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $tag_name_clean));
                    
                    // Kiểm tra thẻ đã tồn tại chưa
                    $stmt_find->execute([$tag_slug]);
                    $tag_row = $stmt_find->fetch();
                    
                    $tag_id = 0;
                    if ($tag_row) {
                        $tag_id = $tag_row['id'];
                    } else {
                        // Tạo thẻ mới nếu chưa có
                        $stmt_insert_tag->execute([$tag_name_clean, $tag_slug]);
                        $tag_id = $pdo->lastInsertId();
                    }
                    
                    // Liên kết bài viết với thẻ
                    if ($tag_id > 0) {
                        $stmt_link->execute([$post_id, $tag_id]);
                    }
                }
            }
            // -----------------------------
            
            // Nếu mọi thứ thành công, commit transaction
            $pdo->commit();
            
            $_SESSION['post_success'] = 'Bài viết của bạn đã được gửi và đang chờ duyệt.';
            header('Location: congdong.php');
            exit;
            
        } catch (Exception $e) {
            // Nếu có lỗi, rollback
            $pdo->rollBack();
            $message = 'Lỗi khi lưu bài viết: ' . $e->getMessage();
            $message_type = 'error';
        }

    } else {
        if (empty($message)) {
            $message = 'Vui lòng chọn một hình ảnh hoặc video để gửi.';
            $message_type = 'error';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Ức - CỔNG THÔNG TIN TRUNG TÂM TƯ LIỆU VÀ KÝ ỨC CỘNG ĐỒNG</title>
        <link rel="shortcut icon" type="image/x-icon" href="image/AVATA A80 TRON.png" />
    <!-- <link rel="stylesheet" href="dangkyuc.css"> ĐÃ XÓA -->
    
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
        main h1, main h3 { color: #333; }

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
        
        /* === KẾT THÚC CSS CHUNG === */

        /* === BẮT ĐẦU CSS TRANG ĐĂNG BÀI === */

        /* Nền */
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

        /* Khung nội dung trắng */
        main .content-wrapper {
            background-color: #fff;
            padding: 20px 25px;
            margin-top: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        /* Tiêu đề form */
        .post-form-container h1 {
            font-size: 1.8em;
            color: #b50202;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .post-form-container p {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 25px;
        }

        /* Form */
        .post-form .form-group {
            margin-bottom: 20px;
        }
        .post-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }
        .post-form input[type="text"],
        .post-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .post-form input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            background-color: #f9f9f9;
            font-size: 14px;
        }
        .post-form textarea {
            resize: vertical;
            min-height: 150px;
        }
        .form-helper-text {
            font-size: 0.9em;
            color: #777;
            display: block;
            margin-top: 6px;
        }

        /* Nút Gửi */
        .btn-submit-memory {
            background-color: #b50202;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn-submit-memory:hover {
            background-color: #9a0202;
        }
        .btn-submit-memory i {
            margin-right: 8px;
        }

        /* Thông báo lỗi/thành công */
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
            }
            .post-form-container h1 {
                font-size: 1.5em;
            }
            .post-form-container p {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>

    <!-- (Phần Header và Nav giữ nguyên) -->
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
                    <li><a href="congdong.php" style="background-color: #9a0202;">Cộng đồng</a></li> 
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

            <section class="post-form-container">
                <h1><i class="fas fa-paper-plane"></i> Gửi Ký Ức của bạn</h1>
                <p>Chia sẻ câu chuyện, hình ảnh hoặc video của bạn với cộng đồng.</p>
                <?php if (!empty($message)): ?>
                    <div class="form-message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- (Form giữ nguyên) -->
                <form action="dangbai.php" method="POST" enctype="multipart/form-data" class="post-form">
                    
                    <div class="form-group">
                        <label for="title">Tiêu đề bài viết:</label>
                        <input type="text" id="title" name="title" placeholder="Nhập tiêu đề..." required 
                               value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="content">Cảm nghĩ / Nội dung:</label>
                        <textarea id="content" name="content" rows="10" placeholder="Viết cảm nghĩ của bạn ở đây..." required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="media_file">Hình ảnh / Video kỷ niệm:</label>
                        <input type="file" id="media_file" name="media_file" 
                               accept="image/png, image/jpeg, image/gif, video/mp4, video/quicktime, video/avi" required>
                        <small class="form-helper-text">Chấp nhận file: .jpg, .png, .gif, .mp4, .mov, .avi (Tối đa 200MB)</small>
                    </div>

                    <div class="form-group">
                        <label for="tags">Thẻ (Tags):</label>
                        <input type="text" id="tags" name="tags" placeholder="Ví dụ: #A80, #Cauchuyencuatoi, #Niemtuhaocuatoi"
                               value="<?php echo isset($tags_input) ? htmlspecialchars($tags_input) : ''; ?>">
                        <small class="form-helper-text">Phân cách các thẻ bằng dấu phẩy (,) hoặc dấu cách.</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn-submit-memory">
                            <i class="fas fa-check"></i> Gửi đi (Chờ duyệt)
                        </button>
                    </div>
                </form>
            </section>

        </div> </main>

    <!-- (Footer giữ nguyên) -->
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