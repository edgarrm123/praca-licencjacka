<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$lecture_id = isset($_GET['lecture_id']) ? intval($_GET['lecture_id']) : 0;
$lecture_name = isset($_GET['lecture_name']) ? $_GET['lecture_name'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'homework';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT t.id, t.name AS task_name, t.description, t.type, t.due_date 
                        FROM tasks t 
                        WHERE t.lecture_id = ? AND t.type = ?");
$stmt->bind_param("is", $lecture_id, $type);
$stmt->execute();
$result_tasks = $stmt->get_result();

include 'parts/headert.php';
include 'parts/sidebart.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(urldecode($lecture_name)); ?> - <?php echo ($type == 'homework') ? 'Prace domowe' : 'Prace samodzielne'; ?> - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/tasks.css">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/uploadTask.css">
</head>
<body>
    <div class="content">
        <h2 style="text-align: center"><?php echo htmlspecialchars(urldecode($lecture_name)); ?> - <?php echo ($type == 'homework') ? 'Prace domowe' : 'Prace samodzielne'; ?></h2>
        
        <div class="upload-task">
            <a href="upload_task.php?lecture_id=<?php echo $lecture_id; ?>&lecture_name=<?php echo urlencode($lecture_name); ?>&type=<?php echo $type; ?>" class="upload-task-button">Dodaj nowe zadanie</a>
        </div>
        
        <?php while ($task = $result_tasks->fetch_assoc()): ?>
            <div class="task-item">
                <div>
                    <a href="teacher_task_details.php?task_id=<?php echo $task['id']; ?>&type=<?php echo $task['type']; ?>">
                        <?php echo htmlspecialchars($task['task_name']); ?>
                    </a>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                </div>
                <p>Termin: <?php echo $task['due_date']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>