<div class="sidebar">
    <a href="teacher_dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'teacher_dashboard.php') ? 'active' : '' ?>">Strona domowa</a>
    <a href="teacher_tasks.php?type=homework" class="<?= (basename($_SERVER['PHP_SELF']) == 'teacher_tasks.php' && isset($_GET['type']) && $_GET['type'] == 'homework') ? 'active' : '' ?>">Prace domowe</a>
    <a href="teacher_tasks.php?type=project" class="<?= (basename($_SERVER['PHP_SELF']) == 'teacher_tasks.php' && isset($_GET['type']) && $_GET['type'] == 'project') ? 'active' : '' ?>">Prace samodzielne</a>
    <a href="teacher_lectures.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'teacher_lectures.php') ? 'active' : '' ?>">Przedmioty</a>
    <a href="archiwum.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'archiwum.php') ? 'active' : '' ?>">Archiwum</a>
</div>