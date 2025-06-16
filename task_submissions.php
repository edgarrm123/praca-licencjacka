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
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if (!$academic_year || !$course_id || !$lecture_id || !$task_id) {
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

$sql = "SELECT t.name AS task_name, t.description, t.due_date
        FROM tasks t
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

$sql = "SELECT u.first_name, u.last_name, 
               ta.status, ta.submission_date, 
               s.id AS submission_id, s.text_answer, s.file_path, 
               g.grade, g.comment AS teacher_comment,
               l.teacher_id, u2.first_name AS teacher_first_name, u2.last_name AS teacher_last_name
        FROM tasks t
        JOIN task_assignments ta ON t.id = ta.task_id
        JOIN users u ON ta.student_id = u.id
        LEFT JOIN submissions s ON ta.id = s.assignment_id
        LEFT JOIN grades g ON s.id = g.submission_id
        JOIN lectures l ON t.lecture_id = l.id
        JOIN users u2 ON l.teacher_id = u2.id
        WHERE t.id = ? 
        AND ta.status = 'graded'
        AND t.due_date BETWEEN ? AND ?
        ORDER BY u.last_name, u.first_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $task_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Archiwum: Odpowiedzi studentów - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .archive-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .submission-details {
            margin: 15px 0;
            padding: 10px;
            border-left: 4px solid #007bff;
            background: #fff;
            border-radius: 4px;
        }

        .submission-details p {
            margin: 5px 0;
        }

        .submission-details img {
            max-width: 200px;
            height: auto;
            margin-top: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="archive-container">
            <h2>Archiwum: Odpowiedzi studentów</h2>
            <h3>Rok akademicki: <?php echo htmlspecialchars($academic_year); ?> > Kurs: <?php echo htmlspecialchars($lecture['course_name']); ?> > Przedmiot: <?php echo htmlspecialchars($lecture['lecture_name']); ?> > Zadanie: <?php echo htmlspecialchars($task['task_name']); ?></h3>
            <p><a href="lecture_submissions.php?academic_year=<?php echo urlencode($academic_year); ?>&course_id=<?php echo $course_id; ?>&lecture_id=<?php echo $lecture_id; ?>">← Powrót do listy zadań</a></p>
            <h4>Opis zadania</h4>
            <p>Opis: <?php echo htmlspecialchars($task['description']); ?></p>
            <p>Termin: <?php echo htmlspecialchars($task['due_date']); ?></p>
            <h4>Odpowiedzi studentów</h4>
            <?php if (!empty($submissions)): ?>
                <?php foreach ($submissions as $submission): ?>
                    <div class="submission-details">
                        <div style="margin-left: 20px;">
                            <p><strong>Student:</strong> <?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']); ?></p>
                            <p><strong>Nauczyciel:</strong> <?php echo htmlspecialchars($submission['teacher_first_name'] . ' ' . $submission['teacher_last_name']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($submission['status']); ?></p>
                            <?php if ($submission['submission_date']): ?>
                                <p><strong>Data przesłania:</strong> <?php echo htmlspecialchars($submission['submission_date']); ?></p>
                            <?php endif; ?>
                            <?php if ($submission['text_answer']): ?>
                                <p><strong>Rozwiązanie:</strong> <?php echo htmlspecialchars($submission['text_answer']); ?></p>
                            <?php endif; ?>
                            <?php if ($submission['file_path']): ?>
                                <p><strong>Przesłany plik:</strong></p>
                                <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($submission['file_path']); ?>" alt="Przesłany plik" onclick="window.open(this.src, '_blank');">
                                </a>
                            <?php endif; ?>
                            <?php if ($submission['grade']): ?>
                                <p><strong>Ocena:</strong> <?php echo htmlspecialchars($submission['grade']); ?></p>
                            <?php endif; ?>
                            <?php if ($submission['teacher_comment']): ?>
                                <p><strong>Komentarz nauczyciela:</strong> <?php echo htmlspecialchars($submission['teacher_comment']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak odpowiedzi studentów ze statusem "graded" dla tego zadania.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>