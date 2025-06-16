<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT l.id, l.name AS lecture_name, c.name AS course_name 
                        FROM lectures l 
                        JOIN course_years cy ON l.course_year_id = cy.id 
                        JOIN courses c ON cy.course_id = c.id 
                        WHERE l.teacher_id = ? 
                        ORDER BY c.name, l.name");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result_lectures = $stmt->get_result();
$stmt->close();

$courses = [];
while ($lecture = $result_lectures->fetch_assoc()) {
    if (!isset($courses[$lecture['course_name']])) {
        $courses[$lecture['course_name']] = [];
    }
    $courses[$lecture['course_name']][] = $lecture;
}

include 'parts/headert.php';
include 'parts/sidebart.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Nauczyciela - Przedmioty - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/tasks.css">
    <link rel="stylesheet" href="styles/styles.css">

</head>
    <body>
        <div class="content">
            <?php 
            foreach ($courses as $course_name => $lectures): ?>
                <div class="course-header"><?php echo htmlspecialchars($course_name); ?></div>
                <?php foreach ($lectures as $lecture): ?>
                    <div class="lecture-item">
                        <a href="teacher_lecture_details.php?lecture_id=<?php echo $lecture['id']; ?>&lecture_name=<?php echo urlencode($lecture['lecture_name']); ?>&type=homework">
                            <?php echo htmlspecialchars($lecture['lecture_name']); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </body>
</html>


