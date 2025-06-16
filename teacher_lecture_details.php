<?php
##################
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$lecture_id = isset($_GET['lecture_id']) ? intval($_GET['lecture_id']) : 0;
$lecture_name = isset($_GET['lecture_name']) ? $_GET['lecture_name'] : '';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT l.id, l.name AS lecture_name, c.name AS course_name, cy.year_id, y.year, cy.id AS course_year_id 
                        FROM lectures l 
                        JOIN course_years cy ON l.course_year_id = cy.id 
                        JOIN courses c ON cy.course_id = c.id 
                        JOIN years y ON cy.year_id = y.id 
                        WHERE l.id = ? AND l.teacher_id = ?");
$stmt->bind_param("ii", $lecture_id, $teacher_id);
$stmt->execute();
$result_lecture = $stmt->get_result();
$lecture = $result_lecture->fetch_assoc();
$stmt->close();

if (!$lecture) {
    echo "<p style='text-align: center; color: red;'>Nie znaleziono wykładu.</p>";
    $conn->close();
    exit;
}

$stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name 
                        FROM users u 
                        JOIN groups g ON u.group_id = g.id 
                        WHERE g.course_year_id = ? AND u.role = 'student'
                        ORDER BY u.last_name, u.first_name");
$stmt->bind_param("i", $lecture['course_year_id']);
$stmt->execute();
$result_students = $stmt->get_result();
$students = $result_students->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo "<p>Debug: Number of students found: " . count($students) . "</p>";

include 'parts/headert.php';
include 'parts/sidebart.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szczegóły Wykładu - <?php echo htmlspecialchars($lecture_name); ?> - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/tasks.css">
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .student-list {
            margin-top: 20px;
        }
        .student-list h3 {
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        .student-list ul {
            list-style: none;
            padding: 0;
        }
        .student-list li {
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        .student-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="content">
        <h2><?php echo htmlspecialchars($lecture['course_name'] . ' - Rok ' . $lecture['year'] . ' - ' . $lecture['lecture_name']); ?></h2>
        <a href="teacher_lectures.php">Powrót do listy przedmiotów</a>

        <div class="student-list">
            <h3>Studenci zapisani na ten kurs i rok</h3>
            <?php if (count($students) > 0): ?>
                <ul>
                    <?php foreach ($students as $student): ?>
                        <li><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Brak studentów zapisanych na ten kurs i rok.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>