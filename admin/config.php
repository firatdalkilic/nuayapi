<?php
$url = parse_url(getenv("JAWSDB_URL"));

$servername = $url["host"] ?? "localhost";
$username = $url["user"] ?? "root";
$password = $url["pass"] ?? "";
$dbname = substr($url["path"], 1) ?? "emlak";

// Veritabanı bağlantısı
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Bağlantı hatası: " . $conn->connect_error);
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
    error_log("Properties table does not exist!");
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