<nav class="navbar">
    <div class="navbar-left">
        <?php include __DIR__ . '/logo.php'; ?>
        <?php include __DIR__ . '/pagination.php'; ?>
    </div>
    <div class="navbar-center">
        <?php
        // Dynamic template loader
        $view = $_GET['view'] ?? 'local';
        $allowedViews = ['local', 'globle'];
        if (!in_array($view, $allowedViews, true)) {
            $view = 'local';
        }
        include __DIR__ . '/' . $view . '.php';
        ?>
    </div>
    <div class="navbar-right">
        <?php include __DIR__ . '/user.php'; ?>
        <?php include __DIR__ . '/dropdown.php'; ?>
    </div>
</nav>
<?php
// Dynamic template loader
$view = $_GET['view'] ?? 'local'; // default to local

// Whitelist allowed views
$allowedViews = ['local', 'globle'];
if (!in_array($view, $allowedViews, true)) {
    $view = 'local';
}

$templateFile = __DIR__ . '/' . $view . '.php';

if (file_exists($templateFile)) {
    include $templateFile;
} else {
    echo "<p>Navbar template not found: " . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . "</p>";
}
?>
