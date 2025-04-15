<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Tablo yapısını kontrol et
    $result = $conn->query("DESCRIBE properties");
    if ($result) {
        echo "<h2>Properties Tablosu Yapısı:</h2>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "Tablo yapısı alınamadı: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 