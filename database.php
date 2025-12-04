<?php
// เริ่ม session ถ้ายังไม่เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$host = "localhost";      // โฮสต์ของฐานข้อมูล
$dbUser = "root";         // ชื่อผู้ใช้ฐานข้อมูล
$dbPass = "";             // รหัสผ่านฐานข้อมูล
$dbName = "mycute_social"; // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ฟังก์ชันช่วยในการแฮชรหัสผ่าน
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// ฟังก์ชันตรวจสอบรหัสผ่าน
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// ฟังก์ชันตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ฟังก์ชันดึงข้อมูลผู้ใช้
function getUser($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>
