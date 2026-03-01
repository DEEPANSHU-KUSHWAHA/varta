<?php
session_start();

function set_flash($message, $type = 'info') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function show_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "<div class='flash {$flash['type']}'>" . htmlspecialchars($flash['message']) . "</div>";
        unset($_SESSION['flash']); // one-time message
    }
}
