<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../admin/config.php';

try {
    // Önce mevcut foreign key'i kaldır
    $dropForeignKeySQL = "ALTER TABLE properties DROP FOREIGN KEY IF EXISTS properties_ibfk_1";
    if ($conn->query($dropForeignKeySQL)) {
        echo "Eski foreign key başarıyla kaldırıldı.\n";
    } else {
        echo "Foreign key zaten kaldırılmış veya mevcut değil.\n";
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