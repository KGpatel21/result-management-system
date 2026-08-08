<?php
// ============================================================
// FILE: db.php
// PURPOSE: Connect to MySQL database
// HOW IT WORKS: Every PHP file includes this file using require_once('db.php')
//               So we write the connection code ONCE and reuse it everywhere.
// ============================================================

// Database connection details (change these if your setup is different)
$host = "localhost";       // Server name (localhost for XAMPP)
$username = "root";        // MySQL username (default in XAMPP is "root")
$password = "";            // MySQL password (default in XAMPP is empty)
$database = "result_db";   // Our database name

// Create connection using mysqli (MySQL Improved)
// mysqli_connect() tries to connect to MySQL using the details above
$conn = mysqli_connect($host, $username, $password, $database);

// Check if connection was successful
// If it fails, stop everything and show an error
if (!$conn) {
    die("<div style='background:#fde8e8; padding:20px; margin:20px; border-radius:8px; border:1px solid #e74c3c; font-family:Arial;'>
        <h3 style='color:#e74c3c;'>Database Connection Failed</h3>
        <p>" . mysqli_connect_error() . "</p>
        <p><strong>Fix:</strong> Open phpMyAdmin, create a database named <code>result_db</code>, then import <code>database.sql</code> into it.</p>
    </div>");
}

// ---------- CHECK IF TABLES EXIST ----------
// This prevents "table doesn't exist" errors on all pages
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admin_users'");
if (mysqli_num_rows($table_check) == 0) {
    die("<div style='background:#fef9e7; padding:20px; margin:20px; border-radius:8px; border:1px solid #f39c12; font-family:Arial;'>
        <h3 style='color:#f39c12;'>Database Tables Not Found</h3>
        <p>The database <code>result_db</code> exists but the tables are missing.</p>
        <p><strong>Fix:</strong> Go to <a href='http://localhost/phpmyadmin'>phpMyAdmin</a> &rarr; select <code>result_db</code> &rarr; click <strong>Import</strong> &rarr; choose <code>database.sql</code> &rarr; click <strong>Go</strong>.</p>
        <p style='color:#888; font-size:13px;'>If you already have old tables, first DROP the database, create it again, then import.</p>
    </div>");
}

// Start a session (sessions are used to remember if someone is logged in)
// session_start() must be called before any HTML output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
