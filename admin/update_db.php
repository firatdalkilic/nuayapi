<?php
require_once 'config.php';

try {
    // Tüm sütunları ve tiplerini tanımla
    $columns = [
        'sheet_no' => ['type' => 'VARCHAR(50)', 'after' => 'parcel_no'],
        'price_per_sqm' => ['type' => 'DECIMAL(12,2)', 'after' => 'net_area'],
        'floor_area_ratio' => ['type' => 'VARCHAR(50)', 'after' => 'net_area'],
        'height_limit' => ['type' => 'VARCHAR(50)', 'after' => 'floor_area_ratio'],
        'eligible_for_credit' => ['type' => 'VARCHAR(10)', 'after' => 'height_limit'],
        'deed_status' => ['type' => 'VARCHAR(50)', 'after' => 'eligible_for_credit'],
        'neighborhood' => ['type' => 'VARCHAR(100)', 'after' => 'deed_status'],
        'zoning_status' => ['type' => 'VARCHAR(50)', 'after' => 'net_area'],
        'block_no' => ['type' => 'VARCHAR(50)', 'after' => 'zoning_status'],
        'parcel_no' => ['type' => 'VARCHAR(50)', 'after' => 'block_no']
    ];

    // Her sütunu kontrol et ve gerekirse ekle
    foreach ($columns as $column_name => $config) {
        $result = $conn->query("SHOW COLUMNS FROM properties LIKE '$column_name'");
        if ($result->num_rows == 0) {
            // Sütun yok, ekle
            $sql = "ALTER TABLE properties ADD COLUMN $column_name {$config['type']} AFTER {$config['after']}";
            if ($conn->query($sql)) {
                echo "$column_name sütunu başarıyla eklendi.<br>";
            } else {
                echo "Hata: $column_name sütunu eklenirken bir hata oluştu - " . $conn->error . "<br>";
            }
        } else {
            // Sütun var, tipini güncelle
            $sql = "ALTER TABLE properties MODIFY COLUMN $column_name {$config['type']}";
            if ($conn->query($sql)) {
                echo "$column_name sütunu başarıyla güncellendi.<br>";
            } else {
                echo "Hata: $column_name sütunu güncellenirken bir hata oluştu - " . $conn->error . "<br>";
            }
        }
    }

    echo "<br>Tüm sütun güncellemeleri tamamlandı.";
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 