<?php
$stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ? AND role = 'teacher'");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher_data= $result->fetch_assoc();
$teacher_name = htmlspecialchars($teacher_data['first_name'] ?? 'Unknown');
$teacher_id = $_SESSION['user_id'];
?>
