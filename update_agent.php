<?php
require_once 'admin/config.php';

try {
    $sql = "UPDATE agents SET 
            sahibinden_store = 'nuayapi',
            emlakjet_profile = 'nua-yapi',
            facebook_username = 'nuayapi' 
            WHERE id = 1";
    
    if ($conn->query($sql)) {
        echo "Danışman bilgileri başarıyla güncellendi.";
        error_log("Danışman güncelleme başarılı: " . $sql);
    } else {
        echo "Hata: " . $conn->error;
        error_log("Danışman güncelleme hatası: " . $conn->error);
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
    error_log("Danışman güncelleme exception: " . $e->getMessage());
}

$conn->close();
?> 