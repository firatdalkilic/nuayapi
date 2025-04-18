<?php
// Hata ayıklama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Config dosyasını dahil et
require_once __DIR__ . '/../admin/config.php';

try {
    // Properties tablosunda video_file sütununu kontrol et ve ekle
    $check_video_file = $conn->query("SHOW COLUMNS FROM properties LIKE 'video_file'");
    if ($check_video_file->num_rows === 0) {
        $conn->query("ALTER TABLE properties ADD COLUMN IF NOT EXISTS video_file VARCHAR(255) DEFAULT NULL AFTER video_call_available");
        echo "video_file sütunu eklendi.\n";
    }

    // Properties tablosunda property_type sütununu kontrol et ve ekle
    $check_property_type = $conn->query("SHOW COLUMNS FROM properties LIKE 'property_type'");
    if ($check_property_type->num_rows === 0) {
        $conn->query("ALTER TABLE properties ADD COLUMN property_type VARCHAR(50) NOT NULL DEFAULT 'Konut' AFTER status");
        echo "property_type sütunu eklendi.\n";
    }

    // Properties tablosunda floor_location sütununu kontrol et ve ekle
    $check_floor_location = $conn->query("SHOW COLUMNS FROM properties LIKE 'floor_location'");
    if ($check_floor_location->num_rows === 0) {
        $conn->query("ALTER TABLE properties ADD COLUMN floor_location VARCHAR(50) DEFAULT NULL AFTER floor");
        echo "floor_location sütunu eklendi.\n";
    }

    // Properties tablosunda credit_eligible sütununu kontrol et ve ekle
    $check_credit_eligible = $conn->query("SHOW COLUMNS FROM properties LIKE 'credit_eligible'");
    if ($check_credit_eligible->num_rows === 0) {
        $conn->query("ALTER TABLE properties ADD COLUMN credit_eligible VARCHAR(10) DEFAULT 'Evet' AFTER heating");
        echo "credit_eligible sütunu eklendi.\n";
    }

    // Properties tablosunda deed_status sütununu kontrol et ve ekle
    $check_deed_status = $conn->query("SHOW COLUMNS FROM properties LIKE 'deed_status'");
    if ($check_deed_status->num_rows === 0) {
        $conn->query("ALTER TABLE properties ADD COLUMN deed_status VARCHAR(50) DEFAULT NULL AFTER credit_eligible");
        echo "deed_status sütunu eklendi.\n";
    }

    // Properties tablosunda neighborhood sütununu kontrol et ve ekle
    $check_neighborhood = $conn->query("SHOW COLUMNS FROM properties LIKE 'neighborhood'");
    if ($check_neighborhood->num_rows === 0) {
        $conn->query("ALTER TABLE properties ADD COLUMN neighborhood VARCHAR(100) NOT NULL AFTER location");
        echo "neighborhood sütunu eklendi.\n";
    }

    echo "Tüm sütun kontrolleri ve eklemeleri tamamlandı.\n";

} catch (Exception $e) {
    echo "Hata oluştu: " . $e->getMessage() . "\n";
}
?> 