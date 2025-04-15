<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    $sql = "ALTER TABLE properties ADD COLUMN price_per_sqm DECIMAL(12,2) DEFAULT NULL";
    
    if ($conn->query($sql)) {
        echo "price_per_sqm sütunu başarıyla eklendi!";
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
?> 