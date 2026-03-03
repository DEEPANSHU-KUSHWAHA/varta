<?php
/**
 * Varta - Modern Messaging SPA
 * Main Entry Point
 */
session_start();

// Check database connection
require_once __DIR__ . '/../resources/db.php';
global $conn;

// If database not connected, show error
if (!$conn || $conn === null) {
    http_response_code(503);
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Service Unavailable</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: linear-gradient(135deg, #0f0f15, #054a3f);
                color: white;
                margin: 0;
            }
            .error-container {
                text-align: center;
                padding: 40px;
                background: rgba(0,0,0,0.3);
                border-radius: 10px;
                max-width: 500px;
            }
            h1 { font-size: 32px; margin: 0 0 16px 0; }
            p { margin: 12px 0; font-size: 16px; }
            .status { color: #ff9800; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>⚠️ Service Unavailable</h1>
            <p class="status">Database Connection Failed</p>
            <p>The application cannot connect to the database.</p>
            <p>Please check your database configuration in <code>.env</code></p>
            <p style="font-size: 12px; color: #999;">Error: ' . htmlspecialchars($conn ? 'Connection OK' : 'No Connection') . '</p>
        </div>
    </body>
    </html>
    ');
}

// Include the main app
include __DIR__ . '/app.php';
