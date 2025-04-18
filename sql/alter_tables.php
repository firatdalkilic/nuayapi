<?php
require_once '../admin/config.php';

// Hata raporlamayı aktifleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // properties tablosuna yeni sütunlar ekleniyor
    $alterQueries = [
        "ALTER TABLE properties 
        ADD COLUMN IF NOT EXISTS square_meters VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS floor VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS floor_location VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS building_age VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS room_count VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS heating VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS credit_eligible VARCHAR(10) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS deed_status VARCHAR(50) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS property_type VARCHAR(50) DEFAULT 'Konut';"
    ];

    // Sorguları çalıştır
    foreach ($alterQueries as $query) {
        if ($conn->query($query) === TRUE) {
            echo "Tablo başarıyla güncellendi: " . $query . "<br>";
        } else {
            throw new Exception("Tablo güncellenirken hata oluştu: " . $conn->error);
        }
    }

    echo "Tüm tablo güncellemeleri başarıyla tamamlandı.";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}

$conn->close();
?> 