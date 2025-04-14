<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'config.php';
    
    if (!isset($conn) || !$conn) {
        throw new Exception("Veritabanı bağlantısı kurulamadı.");
    }

    // display_order kolonunu ekle
    $sql = "ALTER TABLE property_images ADD COLUMN IF NOT EXISTS display_order INT DEFAULT NULL";
    if ($conn->query($sql)) {
        echo "display_order kolonu başarıyla eklendi.";
    } else {
        echo "Hata oluştu: " . $conn->error;
    }

} catch (Exception $e) {
    echo "Bir hata oluştu: " . htmlspecialchars($e->getMessage());
}

$conn->close();
?> 