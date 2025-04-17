<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'admin/config.php';

try {
    // Mevcut tabloları listele
    echo "<h3>Mevcut Tablolar:</h3>";
    $tables = $conn->query("SHOW TABLES");
    while ($table = $tables->fetch_array()) {
        $tableName = $table[0];
        echo "<strong>Tablo: " . $tableName . "</strong><br>";
        
        // Her tablonun yapısını göster
        $columns = $conn->query("SHOW COLUMNS FROM " . $tableName);
        echo "<ul>";
        while ($column = $columns->fetch_assoc()) {
            echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
        }
        echo "</ul>";
        
        // Tablodaki kayıt sayısını göster
        $count = $conn->query("SELECT COUNT(*) as count FROM " . $tableName);
        $countResult = $count->fetch_assoc();
        echo "Kayıt sayısı: " . $countResult['count'] . "<br><br>";
    }
    
    // Agents tablosundaki verileri göster
    echo "<h3>Agents Tablosu Verileri:</h3>";
    $agents = $conn->query("SELECT * FROM agents");
    if ($agents) {
        while ($agent = $agents->fetch_assoc()) {
            echo "<pre>";
            print_r($agent);
            echo "</pre>";
        }
    }

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
    error_log($e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 