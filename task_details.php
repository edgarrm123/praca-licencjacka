<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'student') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['user_id'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text_answer = isset($_POST['text_answer']) ? trim($_POST['text_answer']) : '';
    $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
    $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;

    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = time() . '_' . basename($_FILES['file']['name']);
        $file_path = $upload_dir . $file_name;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $file_path = null;
        }
    }

    if ($submission_id) {
        $sql = "UPDATE submissions SET text_answer = ?, file_path = COALESCE(?, file_path) WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $text_answer, $file_path, $submission_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $sql = "INSERT INTO submissions (assignment_id, text_answer, file_path) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $assignment_id, $text_answer, $file_path);
        $stmt->execute();
        $stmt->close();

        $sql = "UPDATE task_assignments SET status = 'submitted', submission_date = NOW() WHERE id = ? AND student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $assignment_id, $student_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: task_details.php?task_id=$task_id");
    exit;
}

$sql = "SELECT t.id, t.name AS task_name, t.description, t.image_path, t.due_date, 
               l.name AS lecture_name, u.first_name AS teacher_first_name, u.last_name AS teacher_last_name, 
               ta.id AS assignment_id, ta.status, ta.submission_date, 
               s.id AS submission_id, s.text_answer, s.file_path,
               g.grade, g.comment AS teacher_comment
        FROM tasks t
        JOIN lectures l ON t.lecture_id = l.id
        JOIN users u ON l.teacher_id = u.id
        JOIN task_assignments ta ON t.id = ta.task_id
        LEFT JOIN submissions s ON ta.id = s.assignment_id
        LEFT JOIN grades g ON s.id = g.submission_id
        WHERE t.id = ? AND ta.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $task_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->num_rows > 0 ? $result->fetch_assoc() : null;

include 'parts/LoggedUser.php';
include 'parts/header.php';
include 'parts/sidebar.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szczegóły zadania - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/task_details.css">
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        #task_image, #submitted_file {
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

        .description, #solution {
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
    </style>
</head>
<body>
    <div class="content">
        <div class="task-detail-form">
            <?php if ($task): ?>
                <h2><?php echo htmlspecialchars($task['task_name']); ?></h2>
                <div class="teacherinfo">
                    <div>
                        <label for="task_description" style="font-size: 28px;">Treść zadania</label>
                    </div>
                    <div>
                        <p class="teacherinfo">Wykład: <?php echo htmlspecialchars($task['lecture_name']); ?></p>
                        <p class="teacherinfo">Nauczyciel: <?php echo htmlspecialchars($task['teacher_first_name'] . ' ' . $task['teacher_last_name']); ?></p>
                        <p class="teacherinfo">Termin: <?php echo htmlspecialchars($task['due_date']); ?></p>
                    </div>
                </div>
                <textarea id="task_description" name="task_description" rows="4" cols="50" readonly class="description"><?php echo htmlspecialchars($task['description']); ?></textarea>
                <label for="task_image">Zdjęcie zadania</label>
                <?php if (!empty($task['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($task['image_path']); ?>" alt="Zdjęcie zadania" id="task_image" onclick="window.open(this.src, '_blank');">
                <?php else: ?>
                    <p>Brak zdjęcia dla tego zadania.</p>
                <?php endif; ?>

                <?php if ($task['status'] === 'pending'): ?>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="assignment_id" value="<?php echo $task['assignment_id']; ?>">
                        <label for="solution">Komentarz/rozwiązanie:</label>
                        <textarea id="solution" name="text_answer" rows="4" cols="50" class="description" required></textarea>
                        <label for="file">Prześlij plik (opcjonalne):</label>
                        <input type="file" name="file" id="file">
                        <button type="submit">Prześlij rozwiązanie</button>
                    </form>

                <?php elseif ($task['status'] === 'submitted'): ?>
                    <h3>Twoje rozwiązanie</h3>
                    <label for="submitted_solution">Komentarz/rozwiązanie:</label>
                    <textarea id="submitted_solution" name="submitted_solution" rows="4" cols="50" readonly class="description"><?php echo htmlspecialchars($task['text_answer']); ?></textarea>
                    <label for="submitted_file">Przesłany plik:</label>
                    <?php if (!empty($task['file_path'])): ?>
                        <a href="<?php echo htmlspecialchars($task['file_path']); ?>" target="_blank">
                            <img src="<?php echo htmlspecialchars($task['file_path']); ?>" alt="Przesłany plik" id="submitted_file">
                        </a>
                    <?php else: ?>
                        <p>Brak przesłanego pliku.</p>
                    <?php endif; ?>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="assignment_id" value="<?php echo $task['assignment_id']; ?>">
                        <input type="hidden" name="submission_id" value="<?php echo $task['submission_id']; ?>">
                        <label for="solution">Edytuj komentarz/rozwiązanie:</label>
                        <textarea id="solution" name="text_answer" rows="4" cols="50" class="description"><?php echo htmlspecialchars($task['text_answer']); ?></textarea>
                        <label for="file">Edytuj plik (opcjonalne):</label>
                        <input type="file" name="file" id="file">
                        <button type="submit">Zapisz zmiany</button>
                    </form>

                <?php elseif ($task['status'] === 'graded'): ?>
                    <h3>Twoje rozwiązanie</h3>
                    <label for="submitted_solution">Komentarz/rozwiązanie:</label>
                    <textarea id="submitted_solution" name="submitted_solution" rows="4" cols="50" readonly class="description"><?php echo htmlspecialchars($task['text_answer']); ?></textarea>
                    <label for="submitted_file">Przesłany plik:</label>
                    <?php if (!empty($task['file_path'])): ?>
                        <a href="<?php echo htmlspecialchars($task['file_path']); ?>" target="_blank">
                            <img src="<?php echo htmlspecialchars($task['file_path']); ?>" alt="Przesłany plik" id="submitted_file">
                        </a>
                    <?php else: ?>
                        <p>Brak przesłanego pliku.</p>
                    <?php endif; ?>
                    <h3>Ocena i komentarz nauczyciela</h3>
                    <p>Ocena: <?php echo htmlspecialchars($task['grade'] ?? 'Brak oceny'); ?></p>
                    <p>Komentarz nauczyciela: <?php echo htmlspecialchars($task['teacher_comment'] ?? 'Brak komentarza'); ?></p>

                <?php endif; ?>
            <?php else: ?>
                <p>Zadanie nie znalezione lub nie masz do niego dostępu.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>