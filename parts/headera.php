<?php
include 'LoggedAdmin.php';
?>

<link rel="stylesheet" href="styles/header.css">
<header>
    <h1>System Kontroli i Oceny Projektów, Sprawdzianów</h1>
    <nav>
        <a href="#" onclick="toggleNotifications()"><img src="img/notification.svg" alt="Notifications" class="icon"></a>
        <a href="#" class="name"><?php echo $admin_name; ?></a>
        <a href="logout.php"><img src="img/logout.svg" alt="logout" class="icon"></a>
        <div id="notificationsPopup" class="notification-popup">
            <h3>Powiadomienia</h3>
            <p>Masz nowe zadanie do wykonania!</p>
            <p>Twoja ocena została zaktualizowana.</p>
        </div>
    </nav>
</header>

<script>
    function toggleNotifications() {
        var popup = document.getElementById('notificationsPopup');
        popup.classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.matches('.icon')) {
            var popup = document.getElementById('notificationsPopup');
            if (popup.classList.contains('show')) {
                popup.classList.remove('show');
            }
        }
    }
</script>