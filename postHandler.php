<?php
require_once "database.php"; // เชื่อมต่อฐานข้อมูลและ session

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isLoggedIn()) {
    echo json_encode(["status" => "error", "message" => "กรุณาล็อกอินก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// --------------------
// สร้างโพสต์
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $content = trim($_POST['content']);

    if (empty($content)) {
        echo json_encode(["status" => "error", "message" => "โพสต์ไม่สามารถว่างได้"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $content);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "โพสต์สำเร็จ!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการโพสต์"]);
    }
    $stmt->close();
    exit;
}

// --------------------
// ดึงโพสต์
// --------------------
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $stmt = $conn->prepare("SELECT posts.id, posts.content, posts.created_at, users.username 
                            FROM posts 
                            JOIN users ON posts.user_id = users.id
                            ORDER BY posts.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = [];

    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }

    echo json_encode(["status" => "success", "posts" => $posts]);
    $stmt->close();
    exit;
}

// --------------------
// ลบโพสต์ (เฉพาะของผู้ใช้เอง)
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $post_id = intval($_POST['post_id']);

    // ตรวจสอบว่าโพสต์เป็นของผู้ใช้หรือไม่
    $stmt = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $post_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "ลบโพสต์เรียบร้อย"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่สามารถลบโพสต์นี้ได้"]);
    }
    $stmt->close();
    exit;
}

$conn->close();
?>
