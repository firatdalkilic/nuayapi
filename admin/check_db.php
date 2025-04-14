<?php
require_once 'config.php';

try {
    // Tablo yapısını al
    $result = $conn->query("SHOW CREATE TABLE properties");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }

    // Tablo sütunlarını listele
    $result = $conn->query("DESCRIBE properties");
    if ($result) {
        echo "<h3>Tablo Sütunları:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
    }
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 