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

$admin_id = $_SESSION['user_id'];

$action = isset($_GET['action']) ? $_GET['action'] : '';
$section = isset($_GET['section']) ? $_GET['section'] : 'users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        if ($section === 'users') {
            $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $role = trim($_POST['role']);
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $group_id = !empty($_POST['group_id']) ? intval($_POST['group_id']) : null;

            if ($action === 'add') {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, role, first_name, last_name, group_id) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("sssssi", $username, $hashed_password, $role, $first_name, $last_name, $group_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE users SET username = ?, role = ?, first_name = ?, last_name = ?, group_id = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("ssssii", $username, $role, $first_name, $last_name, $group_id, $user_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();

                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        die("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    if (!$stmt->execute()) {
                        die("Execute failed: " . $stmt->error);
                    }
                    $stmt->close();
                }
            }
        } elseif ($section === 'years') {
            $year_id = isset($_POST['year_id']) ? intval($_POST['year_id']) : 0;
            $year = intval($_POST['year']);

            if ($action === 'add') {
                $sql = "INSERT INTO years (year) VALUES (?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("i", $year);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE years SET year = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("ii", $year, $year_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($section === 'courses') {
            $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
            $name = trim($_POST['name']);
            $year_id = intval($_POST['year_id']);

            if ($action === 'add') {
                $sql = "INSERT INTO courses (name, year_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("si", $name, $year_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE courses SET name = ?, year_id = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("sii", $name, $year_id, $course_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($section === 'groups') {
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;
            $course_year_id = intval($_POST['course_year_id']);
            $name = trim($_POST['name']);

            if ($action === 'add') {
                $sql = "INSERT INTO groups (course_year_id, name) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("is", $course_year_id, $name);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE groups SET course_year_id = ?, name = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("isi", $course_year_id, $name, $group_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($section === 'lectures') {
            $lecture_id = isset($_POST['lecture_id']) ? intval($_POST['lecture_id']) : 0;
            $name = trim($_POST['name']);
            $teacher_id = intval($_POST['teacher_id']);
            $course_year_id = intval($_POST['course_year_id']);

            if ($action === 'add') {
                $sql = "INSERT INTO lectures (name, teacher_id, course_year_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("sii", $name, $teacher_id, $course_year_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE lectures SET name = ?, teacher_id = ?, course_year_id = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("siii", $name, $teacher_id, $course_year_id, $lecture_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($section === 'tasks') {
            $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
            $lecture_id = intval($_POST['lecture_id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $type = trim($_POST['type']);
            $due_date = $_POST['due_date'];
            $created_at = $_POST['created_at'];
            $group_id = !empty($_POST['group_id']) ? intval($_POST['group_id']) : null;
            $image_path = trim($_POST['image_path']);

            if ($action === 'add') {
                $sql = "INSERT INTO tasks (lecture_id, name, description, type, due_date, created_at, group_id, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("isssssis", $lecture_id, $name, $description, $type, $due_date, $created_at, $group_id, $image_path);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE tasks SET lecture_id = ?, name = ?, description = ?, type = ?, due_date = ?, created_at = ?, group_id = ?, image_path = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("isssssisi", $lecture_id, $name, $description, $type, $due_date, $created_at, $group_id, $image_path, $task_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($section === 'task_assignments') {
            $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
            $task_id = intval($_POST['task_id']);
            $student_id = intval($_POST['student_id']);
            $assigned_date = $_POST['assigned_date'];
            $submission_date = !empty($_POST['submission_date']) ? $_POST['submission_date'] : null;
            $status = trim($_POST['status']);

            if ($action === 'add') {
                $sql = "INSERT INTO task_assignments (task_id, student_id, assigned_date, submission_date, status) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("iisss", $task_id, $student_id, $assigned_date, $submission_date, $status);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE task_assignments SET task_id = ?, student_id = ?, assigned_date = ?, submission_date = ?, status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("iisssi", $task_id, $student_id, $assigned_date, $submission_date, $status, $assignment_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($section === 'submissions') {
            $submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
            $assignment_id = intval($_POST['assignment_id']);
            $file_path = trim($_POST['file_path']);
            $text_answer = trim($_POST['text_answer']);

            if ($action === 'add') {
                $sql = "INSERT INTO submissions (assignment_id, file_path, text_answer) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("iss", $assignment_id, $file_path, $text_answer);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE submissions SET assignment_id = ?, file_path = ?, text_answer = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("issi", $assignment_id, $file_path, $text_answer, $submission_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($section === 'grades') {
            $grade_id = isset($_POST['grade_id']) ? intval($_POST['grade_id']) : 0;
            $submission_id = intval($_POST['submission_id']);
            $grade = floatval($_POST['grade']);
            $comment = trim($_POST['comment']);

            if ($action === 'add') {
                $sql = "INSERT INTO grades (submission_id, grade, comment) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("ids", $submission_id, $grade, $comment);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            } elseif ($action === 'edit') {
                $sql = "UPDATE grades SET submission_id = ?, grade = ?, comment = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("idsi", $submission_id, $grade, $comment, $grade_id);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        }
        header("Location: admin_control.php?section=$section");
        exit;
    }
}

if ($action === 'delete') {
    if ($section === 'users') {
        $user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'years') {
        $year_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM years WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $year_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'courses') {
        $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $course_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'groups') {
        $group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM groups WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $group_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'lectures') {
        $lecture_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM lectures WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $lecture_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'tasks') {
        $task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $task_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'task_assignments') {
        $assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM task_assignments WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $assignment_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'submissions') {
        $submission_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM submissions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $submission_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($section === 'grades') {
        $grade_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sql = "DELETE FROM grades WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $grade_id);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    }
    header("Location: admin_control.php?section=$section");
    exit;
}

$users = [];
$years = [];
$courses = [];
$groups = [];
$lectures = [];
$tasks = [];
$task_assignments = [];
$submissions = [];
$grades = [];
$teachers = [];
$students = [];

$sql = "SELECT id, year FROM years ORDER BY year";
$result = $conn->query($sql);
if ($result) {
    $years = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (years): " . $conn->error);
}

/*$sql = "SELECT c.id, c.name, y.year 
        FROM courses c 
        JOIN years y ON c.year_id = y.id 
        ORDER BY c.name";
$result = $conn->query($sql);
if ($result) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (courses): " . $conn->error);
}*/

$sql = "SELECT id, name FROM groups ORDER BY name";
$result = $conn->query($sql);
if ($result) {
    $groups = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (groups): " . $conn->error);
}

$sql = "SELECT id, name FROM lectures ORDER BY name";
$result = $conn->query($sql);
if ($result) {
    $lectures = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (lectures): " . $conn->error);
}

$sql = "SELECT id, name FROM tasks ORDER BY name";
$result = $conn->query($sql);
if ($result) {
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (tasks): " . $conn->error);
}

$sql = "SELECT id, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY last_name, first_name";
$result = $conn->query($sql);
if ($result) {
    $teachers = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (teachers): " . $conn->error);
}

$sql = "SELECT id, first_name, last_name FROM users WHERE role = 'student' ORDER BY last_name, first_name";
$result = $conn->query($sql);
if ($result) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (students): " . $conn->error);
}

$sql = "SELECT ta.id, t.name AS task_name, u.first_name, u.last_name 
        FROM task_assignments ta
        JOIN tasks t ON ta.task_id = t.id
        JOIN users u ON ta.student_id = u.id
        ORDER BY t.name";
$result = $conn->query($sql);
if ($result) {
    $task_assignments = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (task_assignments): " . $conn->error);
}

$sql = "SELECT s.id, t.name AS task_name, u.first_name, u.last_name 
        FROM submissions s
        JOIN task_assignments ta ON s.assignment_id = ta.id
        JOIN tasks t ON ta.task_id = t.id
        JOIN users u ON ta.student_id = u.id
        ORDER BY t.name";
$result = $conn->query($sql);
if ($result) {
    $submissions = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Query failed (submissions): " . $conn->error);
}
if ($section === 'users') {
    $sql = "SELECT u.id, u.username, u.role, u.first_name, u.last_name, g.name AS group_name 
            FROM users u 
            LEFT JOIN groups g ON u.group_id = g.id 
            ORDER BY u.last_name, u.first_name";
    $result = $conn->query($sql);
    if ($result) {
        $users = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
} elseif ($section === 'years') {
    $sql = "SELECT id, year FROM years ORDER BY year";
    $result = $conn->query($sql);
    if ($result) {
        $years = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
} elseif ($section === 'courses') {
    $sql = "SELECT c.id, c.name, y.year 
            FROM courses c 
            JOIN course_years cy ON cy.course_id = c.id
            JOIN years y ON cy.year_id = y.id 
            ORDER BY c.name";
    $result = $conn->query($sql);
    if ($result) {
        $courses = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
} elseif ($section === 'groups') {
    $sql = "SELECT g.id, g.name, c.name AS course_name, y.year 
            FROM groups g
            JOIN course_years cy ON g.course_year_id = cy.id
            JOIN courses c ON cy.course_id = c.id
            JOIN years y ON cy.year_id = y.id
            ORDER BY g.name";
    $result = $conn->query($sql);
    if ($result) {
        $groups = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error . " | Query: $sql");
    }
} elseif ($section === 'lectures') {
    $sql = "SELECT l.id, l.name, c.name AS course_name, y.year, u.first_name, u.last_name 
            FROM lectures l
            JOIN course_years cy ON l.course_year_id = cy.id
            JOIN courses c ON cy.course_id = c.id
            JOIN years y ON cy.year_id = y.id
            JOIN users u ON l.teacher_id = u.id
            ORDER BY l.name";
    $result = $conn->query($sql);
    if ($result) {
        $lectures = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
} elseif ($section === 'tasks') {
    $sql = "SELECT t.id, t.name, t.description, t.type, t.due_date, t.created_at, l.name AS lecture_name, g.name AS group_name, t.image_path 
            FROM tasks t
            JOIN lectures l ON t.lecture_id = l.id
            LEFT JOIN groups g ON t.group_id = g.id
            ORDER BY t.name";
    $result = $conn->query($sql);
    if ($result) {
        $tasks = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
} elseif ($section === 'task_assignments') {
    $sql = "SELECT ta.id, t.name AS task_name, u.first_name, u.last_name, ta.assigned_date, ta.submission_date, ta.status 
            FROM task_assignments ta
            JOIN tasks t ON ta.task_id = t.id
            JOIN users u ON ta.student_id = u.id
            ORDER BY ta.assigned_date";
    $result = $conn->query($sql);
    if ($result) {
        $task_assignments = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
} elseif ($section === 'submissions') {
    $sql = "SELECT s.id, t.name AS task_name, u.first_name, u.last_name, s.file_path, s.text_answer 
            FROM submissions s
            JOIN task_assignments ta ON s.assignment_id = ta.id
            JOIN tasks t ON ta.task_id = t.id
            JOIN users u ON ta.student_id = u.id
            ORDER BY t.name";
    $result = $conn->query($sql);
    if ($result) {
        $submissions = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
} elseif ($section === 'grades') {
    $sql = "SELECT g.id, t.name AS task_name, u.first_name, u.last_name, g.grade, g.comment 
            FROM grades g
            JOIN submissions s ON g.submission_id = s.id
            JOIN task_assignments ta ON s.assignment_id = ta.id
            JOIN tasks t ON ta.task_id = t.id
            JOIN users u ON ta.student_id = u.id
            ORDER BY t.name";
    $result = $conn->query($sql);
    if ($result) {
        $grades = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Query failed: " . $conn->error);
    }
}

include 'parts/headera.php';
include 'parts/sidebara.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admina - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .tabs {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tabs a {
            padding: 10px 20px;
            background: #f0f0f0;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }

        .tabs a.active {
            background: #007bff;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .actions a {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .form-container label {
            display: block;
            margin: 10px 0 5px;
        }

        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-container button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-container button:hover {
            opacity: 0.9;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="admin-container">
            <h2>Panel Admina</h2>

            <a href="students_by_course.php" class="button">Lista użytkowników</a>

            <div class="tabs">
                <a href="admin_control.php?section=users" class="<?php echo $section === 'users' ? 'active' : ''; ?>">Użytkownicy</a>
                <a href="admin_control.php?section=years" class="<?php echo $section === 'years' ? 'active' : ''; ?>">Lata akademickie</a>
                <a href="admin_control.php?section=courses" class="<?php echo $section === 'courses' ? 'active' : ''; ?>">Kursy</a>
                <a href="admin_control.php?section=groups" class="<?php echo $section === 'groups' ? 'active' : ''; ?>">Grupy</a>
                <a href="admin_control.php?section=lectures" class="<?php echo $section === 'lectures' ? 'active' : ''; ?>">Przedmioty</a>
                <a href="admin_control.php?section=tasks" class="<?php echo $section === 'tasks' ? 'active' : ''; ?>">Zadania</a>
                <a href="admin_control.php?section=task_assignments" class="<?php echo $section === 'task_assignments' ? 'active' : ''; ?>">Przypisania zadań</a>
                <a href="admin_control.php?section=submissions" class="<?php echo $section === 'submissions' ? 'active' : ''; ?>">Odpowiedzi</a>
                <a href="admin_control.php?section=grades" class="<?php echo $section === 'grades' ? 'active' : ''; ?>">Oceny</a>
            </div>

            <?php if ($section === 'users'): ?>
                <h3>Użytkownicy</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $user = ['id' => 0, 'username' => '', 'role' => 'student', 'first_name' => '', 'last_name' => '', 'group_id' => null];
                    if ($action === 'edit') {
                        $user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, username, role, first_name, last_name, group_id FROM users WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $user_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        $stmt->close();
                        if (!$user) {
                            die("User not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj użytkownika' : 'Edytuj użytkownika'; ?></h4>
                        <form method="post" action="admin_control.php?section=users&action=<?php echo $action; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <label for="username">Nazwa użytkownika:</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <label for="password">Hasło (zostaw puste, aby nie zmieniać):</label>
                            <input type="password" name="password" value="">
                            <label for="role">Rola:</label>
                            <select name="role" required>
                                <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="teacher" <?php echo $user['role'] === 'teacher' ? 'selected' : ''; ?>>Nauczyciel</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <label for="first_name">Imię:</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            <label for="last_name">Nazwisko:</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            <label for="group_id">Grupa:</label>
                            <select name="group_id">
                                <option value="">Brak grupy</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?php echo $group['id']; ?>" <?php echo $group['id'] == $user['group_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($group['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=users&action=add" class="button">Dodaj nowego użytkownika</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Nazwa użytkownika</th>
                                <th>Rola</th>
                                <th>Imię</th>
                                <th>Nazwisko</th>
                                <th>Grupa</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['group_name'] ?: 'Brak'); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=users&action=edit&id=<?php echo $user['id']; ?>">Edytuj</a>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <a href="admin_control.php?section=users&action=delete&id=<?php echo $user['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">Usuń</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'years'): ?>
                <h3>Lata akademickie</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $year = ['id' => 0, 'year' => ''];
                    if ($action === 'edit') {
                        $year_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, year FROM years WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $year_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $year = $result->fetch_assoc();
                        $stmt->close();
                        if (!$year) {
                            die("Year not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj rok akademicki' : 'Edytuj rok akademicki'; ?></h4>
                        <form method="post" action="admin_control.php?section=years&action=<?php echo $action; ?>">
                            <input type="hidden" name="year_id" value="<?php echo $year['id']; ?>">
                            <label for="year">Rok:</label>
                            <input type="number" name="year" value="<?php echo htmlspecialchars($year['year']); ?>" required>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=years&action=add" class="button">Dodaj nowy rok akademicki</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Rok</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($years as $year): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($year['year']); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=years&action=edit&id=<?php echo $year['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=years&action=delete&id=<?php echo $year['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć ten rok akademicki?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'courses'): ?>
                <h3>Kursy</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $course = ['id' => 0, 'name' => '', 'year_id' => 0];
                    if ($action === 'edit') {
                        $course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, name, year_id FROM courses WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $course_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $course = $result->fetch_assoc();
                        $stmt->close();
                        if (!$course) {
                            die("Course not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj kurs' : 'Edytuj kurs'; ?></h4>
                        <form method="post" action="admin_control.php?section=courses&action=<?php echo $action; ?>">
                            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                            <label for="name">Nazwa:</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($course['name']); ?>" required>
                            <label for="year_id">Rok akademicki:</label>
                            <select name="year_id" required>
                                <?php foreach ($years as $year): ?>
                                    <option value="<?php echo $year['id']; ?>" <?php echo $year['id'] == $course['year_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($year['year']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=courses&action=add" class="button">Dodaj nowy kurs</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Nazwa</th>
                                <th>Rok akademicki</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['name']); ?></td>
                                    <td><?php echo htmlspecialchars($course['year']); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=courses&action=edit&id=<?php echo $course['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=courses&action=delete&id=<?php echo $course['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć ten kurs?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'groups'): ?>
                <h3>Grupy</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $group = ['id' => 0, 'course_year_id' => 0, 'name' => ''];
                    if ($action === 'edit') {
                        $group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, course_year_id, name FROM groups WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $group_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $group = $result->fetch_assoc();
                        $stmt->close();
                        if (!$group) {
                            die("Group not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj grupę' : 'Edytuj grupę'; ?></h4>
                        <form method="post" action="admin_control.php?section=groups&action=<?php echo $action; ?>">
                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                            <label for="course_year_id">Kurs:</label>
                            <select name="course_year_id" required>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $course['id'] == $group['course_year_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['name'] . ' (' . $course['year'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="name">Nazwa grupy:</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($group['name']); ?>" required>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=groups&action=add" class="button">Dodaj nową grupę</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Nazwa grupy</th>
                                <th>Kurs</th>
                                <th>Rok akademicki</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groups as $group): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($group['name']); ?></td>
                                    <td><?php echo htmlspecialchars($group['course_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($group['year'] ?? 'Brak'); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=groups&action=edit&id=<?php echo $group['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=groups&action=delete&id=<?php echo $group['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć tę grupę?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'lectures'): ?>
                <h3>Przedmioty</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $lecture = ['id' => 0, 'name' => '', 'course_year_id' => 0, 'teacher_id' => 0];
                    if ($action === 'edit') {
                        $lecture_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, name, course_year_id, teacher_id FROM lectures WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $lecture_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $lecture = $result->fetch_assoc();
                        $stmt->close();
                        if (!$lecture) {
                            die("Lecture not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj przedmiot' : 'Edytuj przedmiot'; ?></h4>
                        <form method="post" action="admin_control.php?section=lectures&action=<?php echo $action; ?>">
                            <input type="hidden" name="lecture_id" value="<?php echo $lecture['id']; ?>">
                            <label for="name">Nazwa:</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($lecture['name']); ?>" required>
                            <label for="course_year_id">Kurs:</label>
                            <select name="course_year_id" required>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $course['id'] == $lecture['course_year_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['name'] . ' (' . $course['year'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="teacher_id">Nauczyciel:</label>
                            <select name="teacher_id" required>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>" <?php echo $teacher['id'] == $lecture['teacher_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=lectures&action=add" class="button">Dodaj nowy przedmiot</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Nazwa</th>
                                <th>Kurs</th>
                                <th>Rok akademicki</th>
                                <th>Nauczyciel</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lectures as $lecture): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lecture['name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($lecture['course_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($lecture['year'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($lecture['first_name'] . ' ' . $lecture['last_name'] ?? 'Brak'); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=lectures&action=edit&id=<?php echo $lecture['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=lectures&action=delete&id=<?php echo $lecture['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć ten przedmiot?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'tasks'): ?>
                <h3>Zadania</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $task = ['id' => 0, 'lecture_id' => 0, 'name' => '', 'description' => '', 'type' => 'homework', 'due_date' => '', 'created_at' => date('Y-m-d H:i:s'), 'group_id' => null, 'image_path' => ''];
                    if ($action === 'edit') {
                        $task_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, lecture_id, name, description, type, due_date, created_at, group_id, image_path FROM tasks WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $task_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $task = $result->fetch_assoc();
                        $stmt->close();
                        if (!$task) {
                            die("Task not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj zadanie' : 'Edytuj zadanie'; ?></h4>
                        <form method="post" action="admin_control.php?section=tasks&action=<?php echo $action; ?>">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <label for="lecture_id">Przedmiot:</label>
                            <select name="lecture_id" required>
                                <?php foreach ($lectures as $lecture): ?>
                                    <option value="<?php echo $lecture['id']; ?>" <?php echo $lecture['id'] == $task['lecture_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lecture['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="name">Nazwa:</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($task['name']); ?>" required>
                            <label for="description">Opis:</label>
                            <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea>
                            <label for="type">Typ:</label>
                            <select name="type" required>
                                <option value="homework" <?php echo $task['type'] === 'homework' ? 'selected' : ''; ?>>Praca domowa</option>
                                <option value="project" <?php echo $task['type'] === 'project' ? 'selected' : ''; ?>>Projekt</option>
                            </select>
                            <label for="due_date">Termin oddania:</label>
                            <input type="datetime-local" name="due_date" value="<?php echo htmlspecialchars(str_replace(' ', 'T', $task['due_date'])); ?>" required>
                            <label for="created_at">Data utworzenia:</label>
                            <input type="datetime-local" name="created_at" value="<?php echo htmlspecialchars(str_replace(' ', 'T', $task['created_at'])); ?>" required>
                            <label for="group_id">Grupa:</label>
                            <select name="group_id">
                                <option value="">Brak grupy</option>
                                <?php foreach ($groups as $group): ?>
                                    <option value="<?php echo $group['id']; ?>" <?php echo $group['id'] == $task['group_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($group['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="image_path">Ścieżka do obrazu:</label>
                            <input type="text" name="image_path" value="<?php echo htmlspecialchars($task['image_path']); ?>">
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=tasks&action=add" class="button">Dodaj nowe zadanie</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Nazwa</th>
                                <th>Opis</th>
                                <th>Typ</th>
                                <th>Termin</th>
                                <th>Utworzono</th>
                                <th>Przedmiot</th>
                                <th>Grupa</th>
                                <th>Obraz</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($task['description'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($task['type'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($task['due_date'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($task['created_at'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($task['lecture_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($task['group_name'] ?: 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($task['image_path'] ?: 'Brak'); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=tasks&action=edit&id=<?php echo $task['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=tasks&action=delete&id=<?php echo $task['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć to zadanie?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'task_assignments'): ?>
                <h3>Przypisania zadań</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $assignment = ['id' => 0, 'task_id' => 0, 'student_id' => 0, 'assigned_date' => date('Y-m-d H:i:s'), 'submission_date' => null, 'status' => 'pending'];
                    if ($action === 'edit') {
                        $assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, task_id, student_id, assigned_date, submission_date, status FROM task_assignments WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $assignment_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $assignment = $result->fetch_assoc();
                        $stmt->close();
                        if (!$assignment) {
                            die("Assignment not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj przypisanie zadania' : 'Edytuj przypisanie zadania'; ?></h4>
                        <form method="post" action="admin_control.php?section=task_assignments&action=<?php echo $action; ?>">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                            <label for="task_id">Zadanie:</label>
                            <select name="task_id" required>
                                <?php foreach ($tasks as $task): ?>
                                    <option value="<?php echo $task['id']; ?>" <?php echo $task['id'] == $assignment['task_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($task['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="student_id">Student:</label>
                            <select name="student_id" required>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo $student['id'] == $assignment['student_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="assigned_date">Data przypisania:</label>
                            <input type="datetime-local" name="assigned_date" value="<?php echo htmlspecialchars(str_replace(' ', 'T', $assignment['assigned_date'])); ?>" required>
                            <label for="submission_date">Data oddania:</label>
                            <input type="datetime-local" name="submission_date" value="<?php echo htmlspecialchars(str_replace(' ', 'T', $assignment['submission_date'] ?? '')); ?>">
                            <label for="status">Status:</label>
                            <select name="status" required>
                                <option value="pending" <?php echo $assignment['status'] === 'pending' ? 'selected' : ''; ?>>Oczekujące</option>
                                <option value="submitted" <?php echo $assignment['status'] === 'submitted' ? 'selected' : ''; ?>>Oddane</option>
                                <option value="graded" <?php echo $assignment['status'] === 'graded' ? 'selected' : ''; ?>>Ocenione</option>
                            </select>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=task_assignments&action=add" class="button">Dodaj nowe przypisanie</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Zadanie</th>
                                <th>Student</th>
                                <th>Data przypisania</th>
                                <th>Data oddania</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($task_assignments as $assignment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['task_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['assigned_date'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['submission_date'] ?: 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['status'] ?? 'Brak'); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=task_assignments&action=edit&id=<?php echo $assignment['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=task_assignments&action=delete&id=<?php echo $assignment['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć to przypisanie?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'submissions'): ?>
                <h3>Odpowiedzi</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $submission = ['id' => 0, 'assignment_id' => 0, 'file_path' => '', 'text_answer' => ''];
                    if ($action === 'edit') {
                        $submission_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, assignment_id, file_path, text_answer FROM submissions WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $submission_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $submission = $result->fetch_assoc();
                        $stmt->close();
                        if (!$submission) {
                            die("Submission not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj odpowiedź' : 'Edytuj odpowiedź'; ?></h4>
                        <form method="post" action="admin_control.php?section=submissions&action=<?php echo $action; ?>">
                            <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">
                            <label for="assignment_id">Przypisanie zadania:</label>
                            <select name="assignment_id" required>
                                <?php foreach ($task_assignments as $assignment): ?>
                                    <option value="<?php echo $assignment['id']; ?>" <?php echo $assignment['id'] == $submission['assignment_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($assignment['task_name'] . ' - ' . $assignment['first_name'] . ' ' . $assignment['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="file_path">Ścieżka do pliku:</label>
                            <input type="text" name="file_path" value="<?php echo htmlspecialchars($submission['file_path']); ?>">
                            <label for="text_answer">Odpowiedź tekstowa:</label>
                            <textarea name="text_answer"><?php echo htmlspecialchars($submission['text_answer']); ?></textarea>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=submissions&action=add" class="button">Dodaj nową odpowiedź</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Zadanie</th>
                                <th>Student</th>
                                <th>Ścieżka do pliku</th>
                                <th>Odpowiedź tekstowa</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($submission['task_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($submission['file_path'] ?: 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($submission['text_answer'] ?: 'Brak'); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=submissions&action=edit&id=<?php echo $submission['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=submissions&action=delete&id=<?php echo $submission['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć tę odpowiedź?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($section === 'grades'): ?>
                <h3>Oceny</h3>
                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <?php
                    $grade = ['id' => 0, 'submission_id' => 0, 'grade' => 0, 'comment' => ''];
                    if ($action === 'edit') {
                        $grade_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                        $sql = "SELECT id, submission_id, grade, comment FROM grades WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $grade_id);
                        if (!$stmt->execute()) {
                            die("Execute failed: " . $stmt->error);
                        }
                        $result = $stmt->get_result();
                        $grade = $result->fetch_assoc();
                        $stmt->close();
                        if (!$grade) {
                            die("Grade not found");
                        }
                    }
                    ?>
                    <div class="form-container">
                        <h4><?php echo $action === 'add' ? 'Dodaj ocenę' : 'Edytuj ocenę'; ?></h4>
                        <form method="post" action="admin_control.php?section=grades&action=<?php echo $action; ?>">
                            <input type="hidden" name="grade_id" value="<?php echo $grade['id']; ?>">
                            <label for="submission_id">Odpowiedź:</label>
                            <select name="submission_id" required>
                                <?php foreach ($submissions as $submission): ?>
                                    <option value="<?php echo $submission['id']; ?>" <?php echo $submission['id'] == $grade['submission_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($submission['task_name'] . ' - ' . $submission['first_name'] . ' ' . $submission['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="grade">Ocena:</label>
                            <input type="number" step="0.1" name="grade" value="<?php echo htmlspecialchars($grade['grade']); ?>" required>
                            <label for="comment">Komentarz:</label>
                            <textarea name="comment"><?php echo htmlspecialchars($grade['comment']); ?></textarea>
                            <button type="submit">Zapisz</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p><a href="admin_control.php?section=grades&action=add" class="button">Dodaj nową ocenę</a></p>
                    <table>
                        <thead>
                            <tr>
                                <th>Zadanie</th>
                                <th>Student</th>
                                <th>Ocena</th>
                                <th>Komentarz</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($grade['task_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($grade['grade'] ?? 'Brak'); ?></td>
                                    <td><?php echo htmlspecialchars($grade['comment'] ?: 'Brak'); ?></td>
                                    <td class="actions">
                                        <a href="admin_control.php?section=grades&action=edit&id=<?php echo $grade['id']; ?>">Edytuj</a>
                                        <a href="admin_control.php?section=grades&action=delete&id=<?php echo $grade['id']; ?>" onclick="return confirm('Czy na pewno chcesz usunąć tę ocenę?');">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>