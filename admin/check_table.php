<?php
require_once 'config.php';

// Tablo yapısını kontrol et
$query = "SHOW CREATE TABLE properties";
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "Tablo yapısı alınamadı: " . $conn->error;
}

// Tablo sütunlarını kontrol et
$query = "SHOW COLUMNS FROM properties";
$result = $conn->query($query);

if ($result) {
    echo "<h3>Tablo Sütunları:</h3>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Sütun bilgileri alınamadı: " . $conn->error;
}

$conn->close();
?> 