<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'homework';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT l.id, l.name AS lecture_name, c.name AS course_name, y.year AS course_year 
                        FROM lectures l 
                        JOIN course_years cy ON l.course_year_id = cy.id 
                        JOIN courses c ON cy.course_id = c.id 
                        JOIN years y ON cy.year_id = y.id 
                        WHERE l.teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result_lectures = $stmt->get_result();
$stmt->close();

include 'parts/headert.php';
include 'parts/sidebart.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($type == 'homework') ? 'Prace domowe' : 'Prace samodzielne'; ?> - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/tasks.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <div class="content">
        <h2 style="text-align: center"><?php echo ($type == 'homework') ? 'Prace domowe' : 'Prace samodzielne'; ?></h2>
        
        <div class="lectures-list">
            <h3>Wybierz wykład:</h3>
            <?php while ($lecture = $result_lectures->fetch_assoc()): ?>
                <div class="lecture-item">
                    <a href="teacher_lecture_tasks.php?lecture_id=<?php echo $lecture['id']; ?>&lecture_name=<?php echo urlencode($lecture['lecture_name']); ?>&type=<?php echo $type; ?>">
                        <?php echo htmlspecialchars($lecture['course_name'] . ' rok ' . $lecture['course_year'] . ' - ' . $lecture['lecture_name']); ?>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>