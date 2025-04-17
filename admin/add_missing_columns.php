<?php
require_once 'config.php';

try {
    // Her kolon için önce varlığını kontrol et, yoksa ekle
    $columns = [
        ["name" => "location", "type" => "VARCHAR(255)", "default" => "NULL"],
        ["name" => "video_file", "type" => "VARCHAR(255)", "default" => "NULL"],
        ["name" => "parking", "type" => "VARCHAR(50)", "default" => "NULL"],
        ["name" => "net_area", "type" => "DECIMAL(10,2)", "default" => "NULL"],
        ["name" => "neighborhood", "type" => "VARCHAR(255)", "default" => "NULL"],
        ["name" => "heating", "type" => "VARCHAR(100)", "default" => "NULL"],
        ["name" => "site_name", "type" => "VARCHAR(255)", "default" => "NULL"],
        ["name" => "room_count", "type" => "INT", "default" => "NULL"],
        ["name" => "living_room", "type" => "INT", "default" => "NULL"],
        ["name" => "bathroom_count", "type" => "INT", "default" => "NULL"],
        ["name" => "building_age", "type" => "VARCHAR(50)", "default" => "NULL"],
        ["name" => "floor_location", "type" => "VARCHAR(50)", "default" => "NULL"],
        ["name" => "total_floors", "type" => "INT", "default" => "NULL"],
        ["name" => "balcony", "type" => "VARCHAR(50)", "default" => "NULL"],
        ["name" => "furnished", "type" => "VARCHAR(50)", "default" => "NULL"],
        ["name" => "site_status", "type" => "VARCHAR(50)", "default" => "NULL"]
    ];

    foreach ($columns as $column) {
        // Önce kolonun var olup olmadığını kontrol et
        $check_query = "SHOW COLUMNS FROM properties LIKE '{$column['name']}'";
        $result = $conn->query($check_query);
        
        if ($result->num_rows == 0) {
            // Kolon yoksa ekle
            $alter_query = "ALTER TABLE properties ADD COLUMN {$column['name']} {$column['type']} DEFAULT {$column['default']}";
            if ($conn->query($alter_query)) {
                echo "Kolon başarıyla eklendi: {$column['name']}<br>";
            } else {
                echo "Hata oluştu ({$column['name']}): " . $conn->error . "<br>";
            }
        } else {
            echo "Kolon zaten mevcut: {$column['name']}<br>";
        }
    }

    echo "<br>İşlem tamamlandı!";

} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
}

$conn->close();
?> 