<?php
require_once '../admin/config.php';

// Hata raporlamayı aktifleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Önce mevcut sütunları kontrol et
    $table = "properties";
    $result = $conn->query("SHOW COLUMNS FROM $table");
    $existing_columns = [];
    while($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }

    // Eklenecek sütunlar
    $columns = [
        'square_meters' => 'VARCHAR(50) DEFAULT NULL',
        'floor' => 'VARCHAR(50) DEFAULT NULL',
        'floor_location' => 'VARCHAR(50) DEFAULT NULL',
        'building_age' => 'VARCHAR(50) DEFAULT NULL',
        'room_count' => 'VARCHAR(50) DEFAULT NULL',
        'heating' => 'VARCHAR(50) DEFAULT NULL',
        'credit_eligible' => 'VARCHAR(10) DEFAULT NULL',
        'deed_status' => 'VARCHAR(50) DEFAULT NULL',
        'property_type' => 'VARCHAR(50) DEFAULT \'Konut\''
    ];

    // Her sütun için ayrı ALTER TABLE komutu
    foreach ($columns as $column => $definition) {
        if (!in_array($column, $existing_columns)) {
            $query = "ALTER TABLE $table ADD COLUMN $column $definition";
            if ($conn->query($query) === TRUE) {
                echo "Sütun başarıyla eklendi: $column<br>";
            } else {
                throw new Exception("Sütun eklenirken hata oluştu ($column): " . $conn->error);
            }
        } else {
            echo "Sütun zaten mevcut: $column<br>";
        }
    }

    echo "<br>Tüm tablo güncellemeleri başarıyla tamamlandı.";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}

$conn->close();
?> 