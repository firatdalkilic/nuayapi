<?php
require_once 'admin/config.php';

try {
    $sql = "UPDATE properties 
            SET square_meters = net_area 
            WHERE property_type = 'İş Yeri' AND net_area IS NOT NULL";
    
    if ($conn->query($sql)) {
        echo "İş yeri ilanları için square_meters değerleri güncellendi!";
    } else {
        echo "Güncelleme sırasında bir hata oluştu: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 