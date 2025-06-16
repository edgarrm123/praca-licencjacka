<?php
session_start();

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, role, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] == 'teacher') {
                header("Location: teacher_dashboard.php");
            } 
            elseif ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            }
            else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error_message = 'Nieprawidłowa nazwa użytkownika lub hasło';
        }
    } else {
        $error_message = 'Nieprawidłowa nazwa użytkownika lub hasło';
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - System Kontroli i Ocena</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="post" action="">
            <h2>Logowanie</h2>
            <?php
            if (isset($error_message)) {
                echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>';
            }
            ?>
            <input type="text" name="username" placeholder="Nazwa użytkownika" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <button type="submit">Zaloguj</button>
        </form>
    </div>
</body>
</html>