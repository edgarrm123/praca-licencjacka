<?php
$stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$name = $stmt->get_result();
$student_data = $name->fetch_assoc();
$student_name = htmlspecialchars($student_data['first_name'] ?? 'Unknown');
?>