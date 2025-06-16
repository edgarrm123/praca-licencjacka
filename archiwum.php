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

$sql = "SELECT DISTINCT 
        CONCAT(
            YEAR(due_date) - IF(MONTH(due_date) < 9, 1, 0), 
            '/', 
            YEAR(due_date) + IF(MONTH(due_date) >= 9, 1, 0)
        ) AS academic_year
        FROM tasks
        WHERE due_date IS NOT NULL
        ORDER BY academic_year DESC";
$result = $conn->query($sql);
$academic_years = $result->fetch_all(MYSQLI_ASSOC);

$years_data = [];
foreach ($academic_years as $year) {
    $academic_year = $year['academic_year'];
    $year_parts = explode('/', $academic_year);
    $start_year = $year_parts[0];
    $end_year = $year_parts[1];

    $start_date = "$start_year-09-01";
    $end_date = "$end_year-08-31 23:59:59";

    $sql = "SELECT DISTINCT c.id, c.name 
            FROM courses c
            JOIN lectures l ON l.course_year_id = c.id
            JOIN tasks t ON t.lecture_id = l.id
            WHERE t.due_date BETWEEN ? AND ?
            ORDER BY c.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $courses_data = [];
    foreach ($courses as $course) {
        $course_id = $course['id'];
        $sql = "SELECT MIN(id) as id, name 
                FROM lectures 
                WHERE course_year_id = ? 
                GROUP BY name 
                ORDER BY name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $lectures = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $courses_data[] = [
            'id' => $course['id'],
            'name' => $course['name'],
            'lectures' => $lectures
        ];
    }

    $years_data[] = [
        'academic_year' => $academic_year,
        'courses' => $courses_data
    ];
}

include 'parts/LoggedTeacher.php';
include 'parts/headert.php';
include 'parts/sidebart.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archiwum - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .archive-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .archive-item {
            margin: 5px 0;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            cursor: pointer;
        }

        .archive-item a {
            text-decoration: none;
            color: #007bff;
        }

        .archive-item a:hover {
            text-decoration: underline;
        }

        .archive-item.hidden {
            display: none !important;
        }

        .archive-item .toggle {
            display: inline-block;
            margin-right: 10px;
            font-weight: bold;
            transition: transform 0.2s;
        }

        .archive-item .toggle.open {
            transform: rotate(90deg);
        }

        .archive-item.clickable:hover {
            background: #e0e0e0;
        }
    </style>
    <script>
        function toggleVisibility(id) {
            const element = document.getElementById(id);
            const toggle = element.parentElement.querySelector('.toggle');
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
                toggle.classList.add('open');
            } else {
                element.classList.add('hidden');
                toggle.classList.remove('open');
            }
        }
    </script>
</head>
<body>
    <div class="content">
        <div class="archive-container">
            <h2>Archiwum</h2>
            <h3>Rok akademicki</h3>
            <?php if (!empty($years_data)): ?>
                <?php foreach ($years_data as $index => $year): ?>
                    <div class="archive-item clickable" onclick="toggleVisibility('year-<?php echo $index; ?>')">
                        <span class="toggle">▶</span>
                        <?php echo htmlspecialchars($year['academic_year']); ?>
                    </div>
                    <div id="year-<?php echo $index; ?>" class="archive-item hidden" style="margin-left: 20px;">
                        <?php if (!empty($year['courses'])): ?>
                            <?php foreach ($year['courses'] as $course): ?>
                                <div class="archive-item clickable" onclick="toggleVisibility('course-<?php echo $course['id']; ?>')">
                                    <span class="toggle">▶</span>
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </div>
                                <div id="course-<?php echo $course['id']; ?>" class="archive-item hidden" style="margin-left: 20px;">
                                    <?php if (!empty($course['lectures'])): ?>
                                        <?php foreach ($course['lectures'] as $lecture): ?>
                                            <div class="archive-item" style="margin-left: 20px;">
                                                <a href="lecture_submissions.php?academic_year=<?php echo urlencode($year['academic_year']); ?>&course_id=<?php echo $course['id']; ?>&lecture_id=<?php echo $lecture['id']; ?>">
                                                    <?php echo htmlspecialchars($lecture['name']); ?>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p>Brak przedmiotów dla tego kursu.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Brak kursów dla tego roku.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak dostępnych lat akademickich.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>