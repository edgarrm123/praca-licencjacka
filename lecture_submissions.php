<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['teacher', 'admin'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$lecture_id = isset($_GET['lecture_id']) ? intval($_GET['lecture_id']) : 0;

if (!$academic_year || !$course_id || !$lecture_id) {
    header("Location: archiwum.php");
    exit;
}

$year_parts = explode('/', $academic_year);
if (count($year_parts) !== 2) {
    header("Location: archiwum.php");
    exit;
}
$start_year = $year_parts[0];
$end_year = $year_parts[1];
$start_date = "$start_year-09-01";
$end_date = "$end_year-08-31 23:59:59";

$sql = "SELECT name FROM lectures WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lecture_id);
$stmt->execute();
$result = $stmt->get_result();
$lecture_name = $result->fetch_assoc()['name'];
$stmt->close();

$sql = "SELECT GROUP_CONCAT(id) AS lecture_ids 
        FROM lectures 
        WHERE name = ? AND course_year_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $lecture_name, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$lecture_ids = $result->fetch_assoc()['lecture_ids'];
$stmt->close();

if (!$lecture_ids) {
    header("Location: archiwum.php");
    exit;
}

$sql = "SELECT DISTINCT t.id, t.name AS task_name, t.description, t.due_date,
               u.first_name AS teacher_first_name, u.last_name AS teacher_last_name
        FROM tasks t
        JOIN task_assignments ta ON t.id = ta.task_id
        JOIN lectures l ON t.lecture_id = l.id
        JOIN users u ON l.teacher_id = u.id
        WHERE t.lecture_id IN ($lecture_ids) 
        AND ta.status = 'graded'
        AND t.due_date BETWEEN ? AND ?
        ORDER BY t.due_date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sql = "SELECT l.name AS lecture_name, c.name AS course_name
        FROM lectures l 
        JOIN courses c ON l.course_year_id = c.id 
        WHERE l.id = ? AND c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $lecture_id, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$lecture = $result->fetch_assoc();
$stmt->close();

include 'parts/LoggedTeacher.php';
include 'parts/headert.php';
include 'parts/sidebart.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archiwum: Zadania - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .archive-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .task-item {
            margin: 10px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            position: relative;
        }

        .task-item a {
            text-decoration: none;
            color: #007bff;
        }

        .task-item a:hover {
            text-decoration: underline;
        }

        .teacher-name {
            position: absolute;
            top: 10px;
            right: 10px; 
            font-size: 14px;
            color: #555;
            text-align: right;
        }

        .task-content {
            margin-right: 150px; 
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="archive-container">
            <h2>Archiwum: Zadania</h2>
            <h3>Rok akademicki: <?php echo htmlspecialchars($academic_year); ?> > Kurs: <?php echo htmlspecialchars($lecture['course_name']); ?> > Przedmiot: <?php echo htmlspecialchars($lecture['lecture_name']); ?></h3>
            <p><a href="archiwum.php">← Powrót do archiwum</a></p>
            <h4>Zadania</h4>
            <?php if (!empty($tasks)): ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item">
                        <div class="teacher-name">
                            <?php echo htmlspecialchars($task['teacher_first_name'] . ' ' . $task['teacher_last_name']); ?>
                        </div>
                        <div class="task-content">
                            <a href="task_submissions.php?academic_year=<?php echo urlencode($academic_year); ?>&course_id=<?php echo $course_id; ?>&lecture_id=<?php echo $lecture_id; ?>&task_id=<?php echo $task['id']; ?>">
                                <?php echo htmlspecialchars($task['task_name']); ?>
                            </a>
                            <p>Opis: <?php echo htmlspecialchars($task['description']); ?></p>
                            <p>Termin: <?php echo htmlspecialchars($task['due_date']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak zadań z odpowiedziami "graded" dla tego przedmiotu.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>