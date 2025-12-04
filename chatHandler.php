<?php
require_once "database.php"; // เชื่อมต่อฐานข้อมูลและ session

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isLoggedIn()) {
    echo json_encode(["status" => "error", "message" => "กรุณาล็อกอินก่อน"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// --------------------
// ส่งข้อความ
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'send') {
    $receiver_id = intval($_POST['receiver_id']);
    $message = trim($_POST['message']);

    if (empty($message)) {
        echo json_encode(["status" => "error", "message" => "ข้อความไม่สามารถว่างได้"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "ส่งข้อความเรียบร้อย"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการส่งข้อความ"]);
    }
    $stmt->close();
    exit;
}

// --------------------
// ดึงข้อความระหว่างผู้ใช้ 2 คน
// --------------------
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $chat_with = intval($_GET['chat_with']);

    $stmt = $conn->prepare("SELECT chats.id, chats.sender_id, chats.receiver_id, chats.message, chats.created_at, users.username AS sender_name
                            FROM chats
                            JOIN users ON chats.sender_id = users.id
                            WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
                            ORDER BY created_at ASC");
    $stmt->bind_param("iiii", $user_id, $chat_with, $chat_with, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $chats = [];
    while ($row = $result->fetch_assoc()) {
        $chats[] = $row;
    }

    echo json_encode(["status" => "success", "chats" => $chats]);
    $stmt->close();
    exit;
}

// --------------------
// ลบข้อความ (เฉพาะของผู้ส่ง)
// --------------------
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $chat_id = intval($_POST['chat_id']);

    $stmt = $conn->prepare("DELETE FROM chats WHERE id=? AND sender_id=?");
    $stmt->bind_param("ii", $chat_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "ลบข้อความเรียบร้อย"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ไม่สามารถลบข้อความนี้ได้"]);
    }
    $stmt->close();
    exit;
}

$conn->close();
?>
