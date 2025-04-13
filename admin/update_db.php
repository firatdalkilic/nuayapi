<?php
require_once 'config.php';

try {
    // sheet_no sütununu ekle (eğer yoksa)
    $sql = "ALTER TABLE properties ADD COLUMN IF NOT EXISTS sheet_no VARCHAR(50) AFTER parcel_no";
    if ($conn->query($sql)) {
        echo "sheet_no sütunu başarıyla eklendi veya zaten mevcuttu.<br>";
    } else {
        echo "Hata: " . $conn->error . "<br>";
    }

    // price_per_sqm sütununu ekle (eğer yoksa)
    $sql = "ALTER TABLE properties ADD COLUMN IF NOT EXISTS price_per_sqm DECIMAL(12,2) AFTER net_area";
    if ($conn->query($sql)) {
        echo "price_per_sqm sütunu başarıyla eklendi veya zaten mevcuttu.<br>";
    } else {
        echo "Hata: " . $conn->error . "<br>";
    }

    // price_per_sqm sütun tipini güncelle
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