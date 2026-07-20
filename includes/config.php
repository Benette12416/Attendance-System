<?php
/**
 * Database configuration
 * Edit these values to match your MySQL / XAMPP / phpMyAdmin setup
 */
$DB_HOST = 'localhost';
$DB_NAME = 'rfid_attendance';
$DB_USER = 'root';
$DB_PASS = '';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

define('GMAIL_ADDRESS', 'you_email_here');
define('GMAIL_APP_PASSWORD', 'your_app_password'); // You can get app password by going to google account and search for app password then create yours

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error() .
        '<br>Make sure MySQL is running and you imported database/attendance.sql');
}

mysqli_set_charset($conn, 'utf8mb4');
date_default_timezone_set('Asia/Manila');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
