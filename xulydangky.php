<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "trungtamtulieu"; 

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Nếu không kết nối được DB, chuyển hướng về trang đăng ký với lỗi "unknown"
    header("Location: dangky.php?error=unknown");
    exit;
}
$conn->set_charset("utf8mb4");

// --- 2. LẤY DỮ LIỆU TỪ FORM ---

// Khởi tạo $fullname để tránh lỗi ở HTML nếu không phải là POST
$fullname = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password_input = $_POST['password']; 
    $confirm_password = $_POST['confirm_password'];

    // --- 3. XÁC THỰC DỮ LIỆU ---

    // 3.1. Kiểm tra mật khẩu có khớp không
    if ($password_input !== $confirm_password) {
        header("Location: dangky.php?error=password_mismatch");
        exit; // Dừng thực thi
    }

    // 3.2. Mã hóa mật khẩu
    $password_hash = password_hash($password_input, PASSWORD_DEFAULT);

    // 3.3. Kiểm tra xem email đã tồn tại chưa
    // Tệp này đã dùng đúng tên 'nguoi_dung'
    $stmt = $conn->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); 

    if ($stmt->num_rows > 0) {
        header("Location: dangky.php?error=duplicate_email");
        $stmt->close();
        $conn->close();
        exit; 
    }
    $stmt->close();

    // --- 4. THÊM DỮ LIỆU VÀO DATABASE ---
    
    // *** SỬA LỖI CHÍNH: Chỉ INSERT các cột có tồn tại trong CSDL mới ***
    // CSDL mới (nguoi_dung) chỉ có: ho_ten, email, mat_khau_bam
    $stmt_insert = $conn->prepare("INSERT INTO nguoi_dung (ho_ten, email, mat_khau_bam) VALUES (?, ?, ?)");
    
    // Cập nhật bind_param từ "ssssss" thành "sss"
    $stmt_insert->bind_param("sss", $fullname, $email, $password_hash);

    if ($stmt_insert->execute()) {
        // *** THAY ĐỔI LOGIC: Đăng ký thành công ***
        // Không chuyển hướng, để script chạy tiếp và hiển thị HTML bên dưới
        // Biến $fullname đã có giá trị và sẽ được dùng ở HTML
        $stmt_insert->close();
        $conn->close();

    } else {
        // Nếu có lỗi CSDL khác, chuyển hướng về với lỗi "unknown"
        header("Location: dangky.php?error=unknown");
        $stmt_insert->close();
        $conn->close();
        exit;
    }

} else {
    // Nếu truy cập trực tiếp , chuyển về trang đăng ký
    header("Location: dangky.php");
    exit;
}
