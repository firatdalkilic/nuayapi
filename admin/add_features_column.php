<?php
require_once 'config.php';

// Heroku JawsDB bağlantısını kullan
$jawsdb_url = getenv("JAWSDB_URL");
$url = parse_url($jawsdb_url);

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = ltrim($url["path"], '/');

// Veritabanına bağlan
$conn = new mysqli($server, $username, $password, $db);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// features sütununu ekle
$sql = "ALTER TABLE properties ADD COLUMN features TEXT AFTER bathroom_count";

if ($conn->query($sql) === TRUE) {
    echo "Features sütunu başarıyla eklendi.";
} else {
    echo "Hata oluştu: " . $conn->error;
}

$conn->close();
?> 