<?php
require_once 'config.php';

try {
    // Önce agent_id sütununun var olup olmadığını kontrol edelim
    $check_column = $conn->query("SHOW COLUMNS FROM properties LIKE 'agent_id'");
    if ($check_column->num_rows == 0) {
        // Sütun yoksa ekleyelim
        $sql = "ALTER TABLE properties ADD COLUMN agent_id INT";
        if ($conn->query($sql)) {
            echo "agent_id sütunu başarıyla eklendi.<br>";
        } else {
            throw new Exception($conn->error);
        }
    } else {
        echo "agent_id sütunu zaten mevcut.<br>";
    }

    // Agents tablosunu yeniden oluşturalım
    $sql = "DROP TABLE IF EXISTS agents";
    if ($conn->query($sql)) {
        echo "Eski agents tablosu silindi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Yeni agents tablosunu oluşturalım
    $sql = "CREATE TABLE agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) {
        echo "Yeni agents tablosu başarıyla oluşturuldu.<br>";
    } else {
        throw new Exception($conn->error);
    }

    // Properties tablosuna foreign key ekleyelim
    $sql = "ALTER TABLE properties ADD FOREIGN KEY (agent_id) REFERENCES agents(id)";
    if ($conn->query($sql)) {
        echo "Foreign key başarıyla eklendi.<br>";
    } else {
        throw new Exception($conn->error);
    }

    echo "Tüm işlemler başarıyla tamamlandı!";
} catch(Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 