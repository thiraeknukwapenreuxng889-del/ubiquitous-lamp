<?php
session_start();
$host = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "mycute_social";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ฟังก์ชันช่วยแฮชรหัสผ่าน
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// ฟังก์ชันตรวจสอบรหัสผ่าน
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ลงทะเบียนผู้ใช้
if (isset($_POST['action']) && $_POST['action'] === 'signup') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ตรวจสอบว่าผู้ใช้มีอยู่แล้วหรือไม่
    $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username หรือ Email ถูกใช้แล้ว"]);
    } else {
        $hashedPass = hashPassword($password);
        $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $username, $email, $hashedPass);
        if ($insert->execute()) {
            echo json_encode(["status" => "success", "message" => "สมัครสมาชิกสำเร็จ!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการสมัครสมาชิก"]);
        }
    }
    $check->close();
}

// ล็อกอินผู้ใช้
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $emailOrUsername = trim($_POST['emailOrUsername']);
    $password = trim($_POST['password']);

    $query = $conn->prepare("SELECT id, username, email, password FROM users WHERE username=? OR email=? LIMIT 1");
    $query->bind_param("ss", $emailOrUsername, $emailOrUsername);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(["status" => "success", "message" => "ล็อกอินสำเร็จ!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่พบผู้ใช้นี้"]);
    }
    $query->close();
}

// ออกจากระบบ
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    echo json_encode(["status" => "success", "message" => "ออกจากระบบเรียบร้อย"]);
}

$conn->close();
?>
