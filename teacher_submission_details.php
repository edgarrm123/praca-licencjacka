<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
    $grade = isset($_POST['grade']) ? intval($_POST['grade']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;

    $sql = "INSERT INTO grades (submission_id, grade, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $submission_id, $grade, $comment);
    $stmt->execute();
    $stmt->close();

    $sql = "UPDATE task_assignments SET status = 'graded' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $stmt->close();

    header("Location: teacher_submission_details.php?task_id=$task_id&student_id=$student_id");
    exit;
}

$sql = "SELECT t.id, t.name AS task_name, t.description, t.type, t.image_path, t.lecture_id, 
               l.name AS lecture_name, u.first_name AS teacher_first_name, u.last_name AS teacher_last_name, 
               t.due_date, s.id AS submission_id, s.text_answer, s.file_path, ta.id AS assignment_id, ta.status, ta.submission_date,
               st.first_name AS student_first_name, st.last_name AS student_last_name
        FROM tasks t 
        LEFT JOIN lectures l ON t.lecture_id = l.id 
        LEFT JOIN users u ON l.teacher_id = u.id
        JOIN task_assignments ta ON t.id = ta.task_id
        JOIN submissions s ON ta.id = s.assignment_id
        JOIN users st ON ta.student_id = st.id
        WHERE t.id = ? AND ta.student_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ii", $task_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

$submission = $result->num_rows > 0 ? $result->fetch_assoc() : null;

$grade_exists = false;
$existing_grade = null;
$existing_comment = null;
if ($submission) {
    $sql = "SELECT grade, comment FROM grades WHERE submission_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $submission['submission_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $grade_exists = true;
        $grade_data = $result->fetch_assoc();
        $existing_grade = $grade_data['grade'];
        $existing_comment = $grade_data['comment'] ?? 'Brak komentarza';
    }
}

include 'parts/headert.php';
include 'parts/sidebart.php';
include 'parts/LoggedTeacher.php';

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Przesłane zadanie - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/task_details.css">
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        #task_image {
            max-width: 200px;
            height: auto;
            margin-top: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .task-detail-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .teacherinfo {
            margin-bottom: 20px;
        }

        .description {
            width: 100%;
            box-sizing: border-box;
            padding: 8px;
            border-radius: 4px;
            font-size: 14px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            background-color: #007bff;
            color: white;
        }

        button:hover {
            opacity: 0.9;
        }

        .file-download {
            margin-top: 10px;
        }

        .file-download a {
            color: #007bff;
            text-decoration: none;
        }

        .file-download a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="content">
        <?php if ($submission): ?>
            <div class="task-detail-form">
                <h2><?php echo htmlspecialchars($submission['task_name']); ?></h2>
                <div class="teacherinfo">
                    <div>
                        <label for="task_description" style="font-size: 28px;">Treść zadania</label>
                    </div>
                    <div>
                        <p class="teacherinfo">Wykład: <?php echo htmlspecialchars($submission['lecture_name'] ?? 'Brak danych'); ?></p>
                        <p class="teacherinfo">Nauczyciel: <?php echo htmlspecialchars(($submission['teacher_first_name'] ?? 'Brak') . ' ' . ($submission['teacher_last_name'] ?? 'danych')); ?></p>
                        <p class="teacherinfo">Student: <?php echo htmlspecialchars($submission['student_first_name'] . ' ' . $submission['student_last_name']); ?></p>
                        <p class="teacherinfo">Termin: <?php echo htmlspecialchars($submission['due_date']); ?></p>
                        <p class="teacherinfo">Data przesłania: <?php echo htmlspecialchars($submission['submission_date']); ?></p>
                    </div>
                </div>
                <textarea id="task_description" name="task_description" rows="4" cols="50" readonly class="description"><?php echo htmlspecialchars($submission['description']); ?></textarea>
                <label for="task_file">Plik zadania</label>
                <?php if (!empty($submission['image_path'])): ?>
                    <?php
                    $file_ext = strtolower(pathinfo($submission['image_path'], PATHINFO_EXTENSION));
                    if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <img src="<?php echo htmlspecialchars($submission['image_path']); ?>" alt="Plik zadania" id="task_image" onclick="window.open(this.src, '_blank');">
                    <?php endif; ?>
                    <div class="file-download">
                        <a href="<?php echo htmlspecialchars($submission['image_path']); ?>" download>Pobierz plik zadania</a>
                    </div>
                <?php else: ?>
                    <p>Brak pliku dla tego zadania.</p>
                <?php endif; ?>
                <label for="submitted_solution">Rozwiązanie studenta</label>
                <textarea id="submitted_solution" name="submitted_solution" rows="4" cols="50" readonly class="description"><?php echo htmlspecialchars($submission['text_answer']); ?></textarea>
                <label for="submitted_file">Przesłany plik studenta</label>
                <?php if (!empty($submission['file_path'])): ?>
                    <div class="file-download">
                        <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" download>Pobierz przesłany plik</a>
                    </div>
                <?php else: ?>
                    <p>Brak przesłanego pliku.</p>
                <?php endif; ?>

                <?php if ($grade_exists): ?>
                    <h3>Ocena</h3>
                    <p>Ocena: <?php echo htmlspecialchars($existing_grade); ?></p>
                    <p>Komentarz: <?php echo htmlspecialchars($existing_comment); ?></p>
                <?php else: ?>
                    <h3>Wystaw ocenę</h3>
                    <form action="" method="post">
                        <input type="hidden" name="submission_id" value="<?php echo $submission['submission_id']; ?>">
                        <input type="hidden" name="assignment_id" value="<?php echo $submission['assignment_id']; ?>">
                        <label for="grade">Ocena (1-5):</label>
                        <input type="number" name="grade" id="grade" min="1" max="5" required>
                        <label for="comment">Komentarz:</label>
                        <textarea id="comment" name="comment" rows="4" cols="50" class="description"></textarea>
                        <button type="submit">Zapisz ocenę</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Przesłane zadanie nie znalezione.</p>
        <?php endif; ?>
    </div>
</body>
</html>