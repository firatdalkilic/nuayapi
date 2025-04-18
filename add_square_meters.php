<?php
require_once 'admin/config.php';

try {
    $sql = "ALTER TABLE properties ADD COLUMN square_meters DECIMAL(10,2) DEFAULT NULL AFTER net_area";
    
    if ($conn->query($sql)) {
        echo "square_meters sütunu başarıyla eklendi!";
    } else {
        echo "Sütun eklenirken bir hata oluştu: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 