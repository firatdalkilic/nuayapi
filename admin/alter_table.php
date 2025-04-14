<?php
require_once 'config.php';

// Veritabanı bağlantısını kontrol et
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// SQL sorgusunu doğrudan çalıştır
$sql = "ALTER TABLE properties MODIFY COLUMN building_age varchar(50)";

// SQL sorgusunu çalıştır
if ($conn->query($sql) === TRUE) {
    echo "Tablo başarıyla güncellendi";
} else {
    echo "Hata: " . $conn->error;
}

$conn->close();
?> 