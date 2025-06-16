<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$teacher_id = $_SESSION['user_id'];

function getColor($percentage) {
    if ($percentage === null) {
        return 'green';
    }
    $grade = $percentage / 20;
    if ($grade < 3) {
        return 'red';
    } elseif ($grade <= 4) {
        return 'orange';
    } else {
        return 'green';
    }
}

# SUBMITTED/CHECKED FOR HOMEWORK
$stmt = $conn->prepare("SELECT COUNT(DISTINCT s.id) as total_submitted, 
                              COUNT(DISTINCT CASE WHEN ta.status IN ('graded') THEN s.id END) as checked 
                       FROM submissions s
                       JOIN task_assignments ta ON s.assignment_id = ta.id
                       JOIN tasks t ON ta.task_id = t.id
                       JOIN lectures l ON t.lecture_id = l.id
                       WHERE t.type = 'homework'
                       AND l.teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result_homework = $stmt->get_result();
$homework_data = $result_homework->fetch_assoc();

# SUBMITTED/CHECKED FOR PROJECT
$stmt = $conn->prepare("SELECT COUNT(DISTINCT s.id) as total_submitted, 
                              COUNT(DISTINCT CASE WHEN ta.status IN ('graded') THEN s.id END) as checked 
                       FROM submissions s
                       JOIN task_assignments ta ON s.assignment_id = ta.id
                       JOIN tasks t ON ta.task_id = t.id
                       JOIN lectures l ON t.lecture_id = l.id
                       WHERE t.type = 'project'
                       AND l.teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result_projects = $stmt->get_result();
$projects_data = $result_projects->fetch_assoc();

# CIRCLE FOR HOMEWORK
$homework_percentage = ($homework_data['total_submitted'] > 0) ? ($homework_data['checked'] / $homework_data['total_submitted']) * 100 : null;
$homework_color = getColor($homework_percentage);

# CIRCLE FOR PROJECT
$projects_percentage = ($projects_data['total_submitted'] > 0) ? ($projects_data['checked'] / $projects_data['total_submitted']) * 100 : null;
$projects_color = getColor($projects_percentage);

include 'parts/headert.php';
include 'parts/sidebart.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Nauczyciela - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <div class="content">
        <h2 style="text-align: center">Panel Nauczyciela</h2>
        <div class="dashboard-stats">
            <div class="circle-statistic <?php echo $homework_color; ?>">
                <div class="stat-number-item"><?php echo $homework_data['checked'] . '/' . $homework_data['total_submitted']; ?></div>
                <div class="stat-label">Sprawdzonych prac domowych</div>
            </div>
            <div class="circle-statistic <?php echo $projects_color; ?>">
                <div class="stat-number-item"><?php echo $projects_data['checked'] . '/' . $projects_data['total_submitted']; ?></div>
                <div class="stat-label">Sprawdzonych projektów</div>
            </div>
        </div>
    </div>
</body>
</html>