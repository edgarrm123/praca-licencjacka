<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PROJEKT";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    header('HTTP/1.1 503 Service Unavailable');
    echo 'Service Unavailable';
    exit;
}