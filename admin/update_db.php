<?php
require_once 'config.php';

try {
    // Önce price_per_sqm sütununu ekle (eğer yoksa)
    $sql = "ALTER TABLE properties ADD COLUMN IF NOT EXISTS price_per_sqm DECIMAL(12,2) AFTER net_area";
    if ($conn->query($sql)) {
        echo "price_per_sqm sütunu başarıyla eklendi veya zaten mevcuttu.<br>";
    } else {
        echo "Hata: " . $conn->error . "<br>";
    }

    // Sütun tipini güncelle
    $sql = "ALTER TABLE properties MODIFY COLUMN price_per_sqm DECIMAL(12,2)";
    if ($conn->query($sql)) {
        echo "price_per_sqm sütunu başarıyla güncellendi.";
    } else {
        echo "Hata: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 