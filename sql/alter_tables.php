<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../admin/config.php';

try {
    // Önce foreign key'leri kontrol et
    $checkForeignKeySQL = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS 
                          WHERE TABLE_NAME = 'properties' 
                          AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
    $result = $conn->query($checkForeignKeySQL);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $dropForeignKeySQL = "ALTER TABLE properties DROP FOREIGN KEY " . $row['CONSTRAINT_NAME'];
            if ($conn->query($dropForeignKeySQL)) {
                echo "Foreign key kaldırıldı: " . $row['CONSTRAINT_NAME'] . "\n";
            }
        }
    }

    // agent_id alanını NULL değer kabul edecek şekilde güncelle
    $alterColumnSQL = "ALTER TABLE properties MODIFY agent_id INT NULL";
    if ($conn->query($alterColumnSQL)) {
        echo "agent_id alanı NULL değer kabul edecek şekilde güncellendi.\n";
    } else {
        throw new Exception($conn->error);
    }

    // Yeni foreign key'i ekle
    $addForeignKeySQL = "ALTER TABLE properties ADD CONSTRAINT properties_agent_fk FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL";
    if ($conn->query($addForeignKeySQL)) {
        echo "Yeni foreign key başarıyla eklendi.\n";
    } else {
        throw new Exception($conn->error);
    }

    $conn->close();
    echo "Veritabanı güncellemesi başarıyla tamamlandı.\n";

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?> 