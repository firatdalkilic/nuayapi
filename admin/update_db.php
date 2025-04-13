<?php
require_once 'config.php';

try {
    // Sütunların varlığını kontrol et
    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'sheet_no'");
    if ($result->num_rows == 0) {
        // sheet_no sütunu yok, ekle
        $sql = "ALTER TABLE properties ADD COLUMN sheet_no VARCHAR(50) AFTER parcel_no";
        if ($conn->query($sql)) {
            echo "sheet_no sütunu başarıyla eklendi.<br>";
        } else {
            echo "Hata: " . $conn->error . "<br>";
        }
    } else {
        echo "sheet_no sütunu zaten mevcut.<br>";
    }

    $result = $conn->query("SHOW COLUMNS FROM properties LIKE 'price_per_sqm'");
    if ($result->num_rows == 0) {
        // price_per_sqm sütunu yok, ekle
        $sql = "ALTER TABLE properties ADD COLUMN price_per_sqm DECIMAL(12,2) AFTER net_area";
        if ($conn->query($sql)) {
            echo "price_per_sqm sütunu başarıyla eklendi.<br>";
        } else {
            echo "Hata: " . $conn->error . "<br>";
        }
    } else {
        // Sütun var, tipini güncelle
        $sql = "ALTER TABLE properties MODIFY COLUMN price_per_sqm DECIMAL(12,2)";
        if ($conn->query($sql)) {
            echo "price_per_sqm sütunu başarıyla güncellendi.<br>";
        } else {
            echo "Hata: " . $conn->error;
        }
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 