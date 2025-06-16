<div class="sidebar">
    <a href="dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">Strona domowa</a>
    <a href="tasks.php?type=homework" class="<?= (basename($_SERVER['PHP_SELF']) == 'tasks.php' && $_GET['type'] == 'homework') ? 'active' : '' ?>">Prace domowe</a>
    <a href="tasks.php?type=project" class="<?= (basename($_SERVER['PHP_SELF']) == 'tasks.php' && $_GET['type'] == 'project') ? 'active' : '' ?>">Prace samodzielne</a>
    <a href="grades.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'grades.php') ? 'active' : '' ?>">Oceny</a>
</div>