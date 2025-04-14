<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    $alterQueries = [
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS floor_location VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS zoning_status VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS block_no VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS parcel_no VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS sheet_no VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS floor_area_ratio VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS height_limit VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS deed_status VARCHAR(255) DEFAULT NULL",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS eligible_for_credit VARCHAR(255) DEFAULT 'Hayır'",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS usage_status ENUM('Boş', 'Kiracılı', 'Mülk Sahibi') DEFAULT 'Boş'",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS video_call_available ENUM('Evet', 'Hayır') DEFAULT 'Hayır'"
    ];

    $success = true;
    $errors = [];

    foreach ($alterQueries as $query) {
        echo "Çalıştırılıyor: " . htmlspecialchars($query) . "<br>";
        if (!$conn->query($query)) {
            $success = false;
            $errors[] = "Sorgu: " . $query . " - Hata: " . $conn->error;
        }
    }

    if ($success) {
        echo "Tüm sütunlar başarıyla eklendi!";
    } else {
        echo "Bazı sütunlar eklenirken hata oluştu:<br>";
        foreach ($errors as $error) {
            echo "- " . htmlspecialchars($error) . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 