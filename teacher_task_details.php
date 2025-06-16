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

$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

$sql = "SELECT t.id, t.name AS task_name, t.description, t.type, t.image_path, t.lecture_id, 
               l.name AS lecture_name, u.first_name AS teacher_first_name, u.last_name AS teacher_last_name, 
               t.due_date
        FROM tasks t 
        JOIN lectures l ON t.lecture_id = l.id 
        JOIN users u ON l.teacher_id = u.id
        WHERE t.id = ? AND l.teacher_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ii", $task_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$task = $result->num_rows > 0 ? $result->fetch_assoc() : null;

include 'parts/headert.php';
include 'parts/sidebart.php';
include 'parts/LoggedTeacher.php';

$stmt->close();
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
        #task_image {
            max-width: 200px;
            height: auto;
            margin-top: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .content {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 20px;
        }

        .task-detail-form {
            flex: 1;
            max-width: 600px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .student-list {
            flex: 1;
            max-width: 400px;
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
        }

        button:hover {
            opacity: 0.9;
        }

        .student-list ul {
            list-style-type: none;
            padding: 0;
        }

        .student-list li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-list a {
            text-decoration: none;
            color: #007bff;
        }

        .student-list a:hover {
            text-decoration: underline;
        }

        .status-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
        }

        .red {
            background-color: #ff0000;
        }

        .orange {
            background-color: #ffa500;
        }

        .green {
            background-color: #008000;
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
            <?php else: ?>
                <p>Zadanie nie znalezione lub nie masz do niego dostępu.</p>
            <?php endif; ?>
        </div>

        <div class="student-list">
            <h3>Lista studentów</h3>
            <?php
            if ($task) {
                $sql = "SELECT u.id, u.first_name, u.last_name, ta.status, s.id AS submission_id
                        FROM users u
                        JOIN task_assignments ta ON u.id = ta.student_id
                        LEFT JOIN submissions s ON ta.id = s.assignment_id
                        WHERE ta.task_id = ? AND u.role = 'student'";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $task_id);
                    $stmt->execute();
                    $student_result = $stmt->get_result();

                    if ($student_result->num_rows > 0) {
                        echo "<ul>";
                        while ($student = $student_result->fetch_assoc()) {
                            $status_class = 'red';
                            if ($student['status'] == 'submitted') {
                                $status_class = 'orange';
                            } elseif ($student['status'] == 'graded') {
                                $status_class = 'green';
                            }
                            echo "<li><span class='status-circle $status_class'></span>";
                            if ($student['submission_id']) {
                                echo "<a href='teacher_submission_details.php?task_id=$task_id&student_id=" . $student['id'] . "'>" . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . "</a>";
                            } else {
                                echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
                            }
                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>Brak studentów przypisanych do tego zadania.</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p>Błąd podczas pobierania listy studentów: " . htmlspecialchars($conn->error) . "</p>";
                }
            } else {
                echo "<p>Nie można wyświetlić listy studentów, ponieważ zadanie nie zostało znalezione.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        function submitTask(taskId) {
            alert('Task ' + taskId + ' submitted! In a real application, you would use AJAX here to submit the task.');
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>