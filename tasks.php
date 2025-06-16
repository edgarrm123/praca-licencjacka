<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'homework';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'red';
        case 'submitted':
            return 'orange';
        case 'graded':
            return 'green';
        default:
            return 'gray';
    }
}

$stmt = $conn->prepare("SELECT t.id, l.name AS lecture_name, t.name AS task_name, t.description, t.type, t.due_date, ta.status AS assignment_status 
                       FROM tasks t
                       JOIN task_assignments ta ON t.id = ta.task_id
                       JOIN lectures l ON t.lecture_id = l.id
                       WHERE t.type = ? AND ta.student_id = ?");
$stmt->bind_param("si", $type, $student_id);
$stmt->execute();
$result = $stmt->get_result();

include 'parts/header.php';
include 'parts/sidebar.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($type == 'homework') ? 'Lista prac domowych' : 'Lista prac samodzielnych'; ?> - System Kontroli i Oceny</title>
    <link rel="stylesheet" href="styles/tasks.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <div class="content">
        <h2 style="text-align: center"><?php echo ($type == 'homework') ? 'Lista prac domowych' : 'Lista prac samodzielnych'; ?></h2>
        <?php while ($task = $result->fetch_assoc()): ?>
            <div class="task-item <?php echo getStatusColor($task['assignment_status']); ?>">
                <div>
                    <a href="task_details.php?task_id=<?php echo $task['id']; ?>&type=<?php echo $task['type']; ?>">
                        <?php echo htmlspecialchars($task['lecture_name']); ?>
                    </a>
                    <p><?php echo htmlspecialchars($task['task_name']); ?></p>
                </div>
                <p>Termin: <?php echo $task['due_date']; ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>