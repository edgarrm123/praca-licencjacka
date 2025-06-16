<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$courses_sql = "SELECT id, name FROM courses";
$courses_result = $conn->query($courses_sql);

$students = [];
$teachers = [];
$selected_course_id = isset($_POST['course_id']) ? $_POST['course_id'] : 0;
$show_all = ($selected_course_id === 'all');

if ($_SERVER["REQUEST_METHOD"] == "POST" && $selected_course_id !== 0) {
    if ($show_all) {
        $teachers_sql = "
            SELECT u.id, u.username, u.first_name, u.last_name, u.role
            FROM users u
            WHERE u.role = 'teacher'
            ORDER BY u.id";
        $teachers_result = $conn->query($teachers_sql);
        $teachers = $teachers_result->fetch_all(MYSQLI_ASSOC);

        $students_sql = "
            SELECT DISTINCT u.id, u.username, u.first_name, u.last_name, u.role, c.name AS course_name, y.year AS course_year, g.name AS group_name
            FROM users u
            LEFT JOIN groups g ON u.group_id = g.id
            LEFT JOIN course_years cy ON g.course_year_id = cy.id
            LEFT JOIN years y ON cy.year_id = y.id
            LEFT JOIN lectures l ON cy.id = l.course_year_id
            LEFT JOIN courses c ON cy.course_id = c.id
            WHERE u.role = 'student'
            ORDER BY u.id";
        $students_result = $conn->query($students_sql);
        $students = $students_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $students_sql = "
            SELECT DISTINCT u.id, u.username, u.first_name, u.last_name, u.role, c.name AS course_name, y.year AS course_year, g.name AS group_name
            FROM users u
            JOIN groups g ON u.group_id = g.id
            JOIN course_years cy ON g.course_year_id = cy.id
            JOIN years y ON cy.year_id = y.id
            JOIN lectures l ON cy.id = l.course_year_id
            JOIN courses c ON cy.course_id = c.id
            WHERE c.id = ? AND u.role = 'student'";
        
        $stmt = $conn->prepare($students_sql);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $students_result = $stmt->get_result();
        $students = $students_result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

include 'parts/LoggedAdmin.php';
include 'parts/headera.php';
include 'parts/sidebara.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Użytkowników - System Kontroli i Oceny Projektów, Sprawdzianów</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        h1, h2 {
            margin-bottom: 20px;
            color: #ffffff; 
        }

        form {
            margin-bottom: 20px;
        }

        select {
            padding: 8px;
            border-radius: 4px;
            font-size: 16px;
            background-color: #2a3f54; 
            color: #ffffff;
            border: 1px solid #007bff; 
        }

        select option {
            background-color: #2a3f54;
            color: #ffffff; 
        }

        input[type="submit"] {
            padding: 8px 16px;
            background-color: #007bff; 
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3; 

        }

        table {
            margin-left: 120px;
            width: 80%;
            max-width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #2a3f54; 
            color: #ffffff;
        }

        th, td {
            padding: 12px;
            border: 1px solid #4a5e74; 
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #1f2a44; 
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #334a66; 
        }

        tr:hover {
            background-color: #3b5577;
        }

        p {
            color: #ffffff; 
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Lista Użytkowników</h1>
        <form action="students_by_course.php" method="post">
            <label for="course_id">Wybierz kurs:</label>
            <select id="course_id" name="course_id" required>
                <option value="">-- Wybierz kurs --</option>
                <option value="all" <?php echo $selected_course_id === 'all' ? 'selected' : ''; ?>>Pokaż wszystkich użytkowników</option>
                <?php
                while ($course = $courses_result->fetch_assoc()) {
                    $selected = ($course['id'] == $selected_course_id) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($course['id']) . '" ' . $selected . '>' . htmlspecialchars($course['name']) . '</option>';
                }
                ?>
            </select>
            <input type="submit" value="Pokaż użytkowników">
        </form>

        <?php if ($show_all): ?>
            <?php if (!empty($teachers)): ?>
                <h2>Nauczyciele</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Nazwa użytkownika</th>
                            <th>Rola</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($teacher['id']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['username']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($students)): ?>
                <h2>Studenci</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Nazwa użytkownika</th>
                            <th>Rola</th>
                            <th>Nazwa kursu</th>
                            <th>Rok kursu</th>
                            <th>Nazwa grupy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['role']); ?></td>
                                <td><?php echo htmlspecialchars($student['course_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['course_year'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['group_name'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (empty($teachers) && empty($students)): ?>
                <p>Brak użytkowników do wyświetlenia.</p>
            <?php endif; ?>

        <?php else: ?>
            <?php if (!empty($students)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Nazwa użytkownika</th>
                            <th>Rola</th>
                            <th>Nazwa kursu</th>
                            <th>Rok kursu</th>
                            <th>Nazwa grupy</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo htmlspecialchars($student['role']); ?></td>
                                <td><?php echo htmlspecialchars($student['course_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['course_year'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['group_name'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                <p>Brak studentów przypisanych do wybranego kursu.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>