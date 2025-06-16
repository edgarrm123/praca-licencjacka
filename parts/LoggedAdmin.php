<?php
$stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$name = $stmt->get_result();
$admin_data = $name->fetch_assoc();
$admin_name = htmlspecialchars($admin_data['first_name'] ?? 'Unknown');
$admin_id = $_SESSION['user_id'];
?>