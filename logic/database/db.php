<?php
require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load the .env file from project root
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad(); // safeLoad() won’t throw error if some vars are missing

// Fallback values if not set in .env
$host     = $_ENV['DB_HOST'] ?? '127.0.0.1';
$user     = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
$database = $_ENV['DB_NAME'] ?? 'brsms_db';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>