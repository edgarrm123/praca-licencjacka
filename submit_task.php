<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
    $student_id = $_SESSION['user_id'];
    $solution = isset($_POST['solution']) ? trim($_POST['solution']) : '';
    $file_path = '';

    if (isset($_FILES['solution_file']) && $_FILES['solution_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['solution_file']['name']);
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['solution_file']['tmp_name'], $file_path)) {
            $_SESSION['message'] = 'Błąd podczas przesyłania pliku.';
            header("Location: student_task_details.php?task_id=$task_id");
            exit;
        }
    }

    $sql = "SELECT id FROM task_assignments WHERE task_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $task_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignment = $result->fetch_assoc();
    $assignment_id = $assignment['id'];

    $sql = "INSERT INTO submissions (assignment_id, file_path, text_answer) 
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $assignment_id, $file_path, $solution);
    
    if ($stmt->execute()) {
        $sql = "UPDATE task_assignments SET status = 'submitted', submission_date = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();
        $_SESSION['message'] = 'Zadanie zostało pomyślnie przesłane!';
    } else {
        $_SESSION['message'] = 'Błąd podczas zapisywania danych: ' . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: task_details.php?task_id=$task_id");
    exit;
}

$conn->close();
?>