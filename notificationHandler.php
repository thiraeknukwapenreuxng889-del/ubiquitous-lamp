<?php
require_once "database.php"; // เชื่อมต่อฐานข้อมูลและ session

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isLoggedIn()) {
    echo json_encode(["status" => "error", "message" => "กรุณาล็อกอินก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// --------------------
// สร้างการแจ้งเตือน
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $content = trim($_POST['content']);

    if (empty($content)) {
        echo json_encode(["status" => "error", "message" => "เนื้อหาการแจ้งเตือนไม่สามารถว่างได้"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $content);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "สร้างการแจ้งเตือนเรียบร้อย"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการสร้างการแจ้งเตือน"]);
    }
    $stmt->close();
    exit;
}

// --------------------
// ดึงการแจ้งเตือนทั้งหมดของผู้ใช้
// --------------------
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $stmt = $conn->prepare("SELECT id, content, created_at 
                            FROM notifications 
                            WHERE user_id=? 
                            ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    echo json_encode(["status" => "success", "notifications" => $notifications]);
    $stmt->close();
    exit;
}

// --------------------
// ลบการแจ้งเตือน
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $notification_id = intval($_POST['notification_id']);

    $stmt = $conn->prepare("DELETE FROM notifications WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $notification_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "ลบการแจ้งเตือนเรียบร้อย"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่สามารถลบการแจ้งเตือนได้"]);
    }
    $stmt->close();
    exit;
}

$conn->close();
?>
