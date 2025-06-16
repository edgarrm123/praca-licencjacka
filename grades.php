<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

include 'parts/LoggedUser.php';
include 'parts/header.php';
include 'parts/sidebar.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Oceny - System Kontroli i Ocena</title>
        <link rel="stylesheet" href="styles/grades.css">
        <link rel="stylesheet" href="styles/styles.css">
    </head>
    <body>
        <div class="content">
            <h2 style="text-align: center">Oceny</h2>
            <?php
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            function getColor($grade) {
                if ($grade < 33) {
                    return 'red';
                } elseif ($grade < 66) {
                    return 'orange';
                } else {
                    return 'green';
                }
            }

            $sql = "SELECT 
                    l.name AS lecture_name, 
                    CONCAT(u.first_name, ' ', u.last_name) AS teacher_name, 
                    AVG(CASE WHEN t.type = 'homework' THEN g.grade ELSE NULL END) AS avg_homework,
                    AVG(CASE WHEN t.type = 'project' THEN g.grade ELSE NULL END) AS avg_project,
                    AVG(g.grade) AS avg_overall
                FROM grades g
                JOIN submissions s ON g.submission_id = s.id
                JOIN task_assignments ta ON s.assignment_id = ta.id
                JOIN tasks t ON ta.task_id = t.id
                JOIN lectures l ON t.lecture_id = l.id
                JOIN users u ON l.teacher_id = u.id
                WHERE ta.student_id = ?
                GROUP BY l.id, l.name, u.first_name, u.last_name
                ORDER BY l.name";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $homework_color = getColor($row['avg_homework']);
                    $project_color = getColor($row['avg_project']);
                    ?>
                    <div class="grade-item">
                        <div class="info">
                            <p><strong><?php echo htmlspecialchars($row['lecture_name']); ?></strong></p>
                            <p><?php echo htmlspecialchars($row['teacher_name']); ?></p>
                        </div>
                        <div class="standart-grade">
                            <p>Średnia z domowych</p>
                            <div class="score <?php echo $homework_color; ?>"><?php echo round($row['avg_homework'], 2); ?></div>
                            <p>Średnia z projektów</p>
                            <div class="score <?php echo $project_color; ?>"><?php echo round($row['avg_project'], 2); ?></div>
                        </div>
                        <a href="#" class="details-btn">Szczegóły</a>
                    </div>
                    <?php
                }
            } else {
                echo "<p>Brak ocen do wyświetlenia</p>";
            }

            $sql_overall = "SELECT AVG(g.grade) AS avg_overall 
                        FROM grades g
                        JOIN submissions s ON g.submission_id = s.id
                        JOIN task_assignments ta ON s.assignment_id = ta.id
                        WHERE ta.student_id = ?";
            $stmt = $conn->prepare($sql_overall);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result_overall = $stmt->get_result();
            $overall_grade = $result_overall->fetch_assoc();

            if ($overall_grade) {
                echo '<div class="average-grade">';
                echo '<p class="text"><strong>Średnia całego kursu:</strong></p>';
                echo '<div class="scoremain ' . getColor($overall_grade['avg_overall']) . '">' . round($overall_grade['avg_overall'], 2) . '</div>';
                echo '</div>';
            }
            $conn->close();
            ?>
        </div>
    </body>
</html>