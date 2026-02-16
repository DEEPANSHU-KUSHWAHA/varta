<nav class="navbar">
    <div class="navbar-left">
        <?php include __DIR__ . '/logo.php'; ?>
        <?php include __DIR__ . '/pagination.php'; ?>
    </div>
    <div class="navbar-right">
        <?php include __DIR__ . '/user.php'; ?>
    </div>
</nav>

<?php
// Dynamic template loader
$view = $_GET['view'] ?? 'local'; // default to local
$templateFile = __DIR__ . "/{$view}.php";

if (file_exists($templateFile)) {
    include $templateFile;
} else {
    echo "<p>Navbar template not found: {$view}</p>";
}
?>
