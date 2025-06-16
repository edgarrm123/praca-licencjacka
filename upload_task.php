<?php
session_start();
require_once 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$groups_sql = "SELECT g.id, g.name FROM groups g 
               JOIN course_years cy ON g.course_year_id = cy.id 
               JOIN lectures l ON cy.id = l.course_year_id 
               WHERE l.id = ?";
$groups_stmt = $conn->prepare($groups_sql);
$groups_stmt->bind_param("i", $lecture_id);
$groups_stmt->execute();
$groups_result = $groups_stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['task_name']) || empty($_POST['description']) ||
        empty($_POST['due_date']) || empty($_POST['group_id'])) {

        echo "<p style='text-align: center; color: red;'>Błąd: Nie wszystkie wymagane pola zostały wypełnione.</p>";

    } else {

        $task_name  = $_POST['task_name'];
        $description = $_POST['description'];
        $due_date    = $_POST['due_date'];
        $group_id    = $_POST['group_id'];
        $file_path   = NULL;

        echo "<p style='text-align: center;'>Otrzymane dane: task_name=$task_name, description=$description, due_date=$due_date, group_id=$group_id, lecture_id=$lecture_id, type=$type</p>";

        if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] === UPLOAD_ERR_OK) {

            $upload_dir   = 'uploads/';
            $max_file_size = 5 * 1024 * 1024;          // 5 MB

            
            $allowed_ext  = ['png', 'jpg', 'jpeg', 'pdf', 'txt', 'cpp'];
            $allowed_mime = [
                'image/png',
                'image/jpeg',
                'application/pdf',
                'text/plain',
                'text/x-c++src', 
                'text/x-csrc'
            ];

            $file_tmp  = $_FILES['task_file']['tmp_name'];
            $file_name = $_FILES['task_file']['name'];
            $file_size = $_FILES['task_file']['size'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $file_type = mime_content_type($file_tmp);

            if ($file_ext === 'cpp') $file_type = 'text/x-c++src';
            if ($file_ext === 'txt') $file_type = 'text/plain';

            if (in_array($file_ext, $allowed_ext, true) &&
                in_array($file_type, $allowed_mime, true) &&
                $file_size <= $max_file_size) {

                $unique_file_name = uniqid('task_file_') . '.' . $file_ext;
                $file_path        = $upload_dir . $unique_file_name;

                if (!move_uploaded_file($file_tmp, $file_path)) {
                    echo "<p style='text-align: center; color: red;'>Błąd przy przesyłaniu pliku.</p>";
                }

            } else {
                echo "<p style='text-align: center; color: red;'>
                        Nieprawidłowy typ pliku lub przekroczony limit 5 MB.
                        Dozwolone: PNG, JPG, PDF, TXT, CPP.
                      </p>";
            }
        }

        $stmt = $conn->prepare(
            "INSERT INTO tasks
             (name, description, type, lecture_id, due_date, group_id, image_path, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        if (!$stmt) {
            echo "<p style='text-align: center; color: red;'>Błąd przygotowania zapytania: {$conn->error}</p>";
        } else {
            $stmt->bind_param("sssisis",
                $task_name, $description, $type,
                $lecture_id, $due_date, $group_id, $file_path
            );

            if ($stmt->execute()) {
                echo "<p style='text-align: center; color: green;'>Zadanie dodane pomyślnie!</p>";

                $task_id = $conn->insert_id;

                $students_sql = "SELECT id FROM users WHERE group_id = ? AND role = 'student'";
                $students_stmt = $conn->prepare($students_sql);

                if ($students_stmt) {
                    $students_stmt->bind_param("i", $group_id);
                    $students_stmt->execute();
                    $students_result = $students_stmt->get_result();

                    $assign_stmt = $conn->prepare(
                        "INSERT INTO task_assignments
                         (task_id, student_id, assigned_date, status)
                         VALUES (?, ?, NOW(), 'pending')"
                    );

                    if ($assign_stmt) {
                        while ($student = $students_result->fetch_assoc()) {
                            $assign_stmt->bind_param("ii", $task_id, $student['id']);
                            $assign_stmt->execute();
                        }
                        $assign_stmt->close();
                        echo "<p style='text-align: center; color: green;'>Zadanie przypisane do studentów!</p>";
                    } else {
                        echo "<p style='text-align: center; color: red;'>Błąd przygotowania zapytania przypisania: {$conn->error}</p>";
                    }
                    $students_stmt->close();
                } else {
                    echo "<p style='text-align: center; color: red;'>Błąd przygotowania zapytania studentów: {$conn->error}</p>";
                }

            } else {
                echo "<p style='text-align: center; color: red;'>Błąd przy dodawaniu zadania: {$stmt->error}</p>";
            }
            $stmt->close();
        }
    }
}


include 'parts/headert.php';
include 'parts/sidebart.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj nowe zadanie - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/uploadTask.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <div class="content">
        <h2 style="text-align: center">Dodaj nowe zadanie dla <?php echo htmlspecialchars(urldecode($lecture_name)); ?></h2>
        <form action="upload_task.php?lecture_id=<?php echo $lecture_id; ?>&lecture_name=<?php echo urlencode($lecture_name); ?>&type=<?php echo $type; ?>" method="post" enctype="multipart/form-data">
            <label for="task_name">Nazwa zadania:</label>
            <input type="text" id="task_name" name="task_name" required>
            
            <label for="description">Opis zadania:</label>
            <textarea id="description" name="description" rows="4" cols="50" required></textarea>
            
            <label for="task_file">Dodaj plik (opcjonalnie):</label>
            <input type="file" id="task_file" name="task_file" accept="image/jpeg,image/png,image/gif,text/x-c++src,application/pdf">
            
            <label for="due_date">Termin:</label>
            <input type="date" id="due_date" name="due_date" required>
            
            <label for="group_id">Przypisz do grupy:</label>
            <select id="group_id" name="group_id" required>
                <?php
                while ($group = $groups_result->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($group['id']) . '">' . htmlspecialchars($group['name']) . '</option>';
                }
                ?>
            </select>
            
            <input type="submit" value="Dodaj zadanie">
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>