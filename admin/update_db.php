<?php
require_once 'config.php';

try {
    // Önce agent_id sütununu ekleyelim (eğer yoksa)
    $sql = "ALTER TABLE properties ADD COLUMN IF NOT EXISTS agent_id INT";
    $pdo->exec($sql);
    echo "agent_id sütunu başarıyla eklendi veya zaten mevcut.<br>";

    // Agents tablosunu yeniden oluşturalım
    $sql = "DROP TABLE IF EXISTS agents";
    $pdo->exec($sql);
    echo "Eski agents tablosu silindi.<br>";

    // Yeni agents tablosunu oluşturalım
    $sql = "CREATE TABLE agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Yeni agents tablosu başarıyla oluşturuldu.<br>";

    // Properties tablosuna foreign key ekleyelim
    $sql = "ALTER TABLE properties ADD FOREIGN KEY (agent_id) REFERENCES agents(id)";
    $pdo->exec($sql);
    echo "Foreign key başarıyla eklendi.<br>";

    echo "Tüm işlemler başarıyla tamamlandı!";
} catch(PDOException $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 