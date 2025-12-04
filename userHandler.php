<?php
require_once "database.php"; // เชื่อมต่อฐานข้อมูลและ session

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isLoggedIn()) {
    echo json_encode(["status" => "error", "message" => "กรุณาล็อกอินก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// --------------------
// ดึงข้อมูลผู้ใช้
// --------------------
if (isset($_GET['action']) && $_GET['action'] === 'get') {
    $user = getUser($conn, $user_id);
    if ($user) {
        echo json_encode(["status" => "success", "user" => $user]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบผู้ใช้"]);
    }
    exit;
}

// --------------------
// อัปเดตโปรไฟล์ผู้ใช้
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($email)) {
        echo json_encode(["status" => "error", "message" => "กรุณากรอกข้อมูลให้ครบ"]);
        exit;
    }

    // ตรวจสอบว่า username หรือ email ซ้ำกับผู้ใช้อื่น
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username=? OR email=?) AND id!=?");
    $stmt->bind_param("ssi", $username, $email, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username หรือ Email ถูกใช้แล้ว"]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // อัปเดตข้อมูล
    $update = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
    $update->bind_param("ssi", $username, $email, $user_id);
    if ($update->execute()) {
        $_SESSION['username'] = $username; // อัปเดต session ด้วย
        echo json_encode(["status" => "success", "message" => "อัปเดตโปรไฟล์เรียบร้อย"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการอัปเดต"]);
    }
    $update->close();
    exit;
}

// --------------------
// เปลี่ยนรหัสผ่าน
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);

    // ดึงรหัสผ่านเก่าจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!verifyPassword($current_password, $hashed_password)) {
        echo json_encode(["status" => "error", "message" => "รหัสผ่านปัจจุบันไม่ถูกต้อง"]);
        exit;
    }

    $new_hashed = hashPassword($new_password);
    $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $update->bind_param("si", $new_hashed, $user_id);
    if ($update->execute()) {
        echo json_encode(["status" => "success", "message" => "เปลี่ยนรหัสผ่านเรียบร้อย"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน"]);
    }
    $update->close();
    exit;
}

$conn->close();
?>
