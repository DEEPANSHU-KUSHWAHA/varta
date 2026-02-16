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
