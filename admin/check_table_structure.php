<?php
require_once 'config.php';

try {
    // Tablo yapısını kontrol et
    $sql = "SHOW CREATE TABLE properties";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<pre>";
        print_r($row['Create Table']);
        echo "</pre>";
        
        // Tablo kolonlarını listele
        echo "<h3>Tablo Kolonları:</h3>";
        $columns = $conn->query("DESCRIBE properties");
        while($column = $columns->fetch_assoc()) {
            echo "<pre>";
            print_r($column);
            echo "</pre>";
        }
    } else {
        echo "Tablo yapısı alınamadı: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}

$conn->close();
?> 