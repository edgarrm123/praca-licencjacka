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

function getColor($grade) {
    if ($grade < 3) {
        return 'red';
    } elseif ($grade <= 4) {
        return 'orange';
    } else {
        return 'green';
    }
}

$student_id = $_SESSION['user_id'];

# COMPLETED/TOTAL FOR HOMEWORK
$stmt = $conn->prepare("SELECT COUNT(DISTINCT t.id) as total, 
                              COUNT(DISTINCT CASE WHEN ta.status IN ('graded') THEN t.id END) as completed 
                       FROM tasks t
                       JOIN task_assignments ta ON t.id = ta.task_id
                       WHERE t.type = 'homework'
                       AND ta.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_homework = $stmt->get_result();
$homework_data = $result_homework->fetch_assoc();

# COMPLETED/TOTAL FOR PROJECT
$stmt = $conn->prepare("SELECT COUNT(DISTINCT t.id) as total, 
                              COUNT(DISTINCT CASE WHEN ta.status IN ('graded') THEN t.id END) as completed 
                       FROM tasks t
                       JOIN task_assignments ta ON t.id = ta.task_id
                       WHERE t.type = 'project'
                       AND ta.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_projects = $stmt->get_result();
$projects_data = $result_projects->fetch_assoc();

# AVG GRADE
$stmt = $conn->prepare("SELECT AVG(g.grade) as avg_grade 
                       FROM grades g
                       JOIN submissions s ON g.submission_id = s.id
                       JOIN task_assignments ta ON s.assignment_id = ta.id
                       WHERE ta.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_grade = $stmt->get_result();
$grade_data = $result_grade->fetch_assoc();

# CIRCLE FOR HOMEWORK
$homework_percentage = ($homework_data['total'] > 0) ? ($homework_data['completed'] / $homework_data['total']) * 100 : 0;
$homework_color = getColor($homework_percentage / 20);

# CIRCLE FOR PROJECT
$projects_percentage = ($projects_data['total'] > 0) ? ($projects_data['completed'] / $projects_data['total']) * 100 : 0;
$projects_color = getColor($projects_percentage / 20);

# CIRCLE FOR GRADE
$avg_grade = round($grade_data['avg_grade'] ?? 0, 1);
$grade_color = getColor($avg_grade); 

include 'parts/LoggedUser.php';
include 'parts/header.php';
include 'parts/sidebar.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <div class="content">
        <h2 style="text-align: center">Panel studenta</h2>
        <div class="dashboard-stats">
            <div class="circle-statistic <?php echo $homework_color; ?>">
                <div class="stat-number-item"><?php echo $homework_data['completed'] . '/' . $homework_data['total']; ?></div>
                <div class="stat-label">Wykonanych prac domowych</div>
            </div>
            <div class="circle-statistic <?php echo $projects_color; ?>">
                <div class="stat-number-item"><?php echo $projects_data['completed'] . '/' . $projects_data['total']; ?></div>
                <div class="stat-label">Wykonanych prac samodzielnych</div>
            </div>
            <div class="circle-statistic grade <?php echo $grade_color; ?>">
                <div class="stat-number-item"><?php echo $avg_grade; ?></div>
                <div class="stat-label">Średnia ocena</div>
            </div>
        </div>
    </div>
</body>
</html>