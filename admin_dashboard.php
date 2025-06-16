<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$admin_id = $_SESSION['user_id'];

include 'parts/headera.php';
include 'parts/sidebara.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admina - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
</body>
</html>