<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "emlak";

// Veritabanı bağlantısı
$conn = new mysqli($servername, $username, $password);

// Bağlantı kontrolü
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Veritabanı seçimi
if (!$conn->select_db($dbname)) {
    // Veritabanı yoksa oluştur
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
}

// Türkçe karakter desteği
$conn->set_charset("utf8mb4");
mysqli_set_charset($conn, "utf8mb4");

// Karakter seti kontrolü
$conn->query("SET NAMES utf8mb4");
$conn->query("SET CHARACTER SET utf8mb4");
$conn->query("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");

// Test query to check if tables exist
$test_query = "SHOW TABLES LIKE 'properties'";
$result = $conn->query($test_query);
if ($result->num_rows == 0) {
    // Properties tablosu yoksa oluştur
    $create_table_query = "CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_table_query);
}

// Oturum kontrolü için fonksiyon
function checkLogin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $_SESSION['error'] = "Lütfen önce giriş yapın.";
        header("Location: login.php");
        exit;
    }
}
?> 