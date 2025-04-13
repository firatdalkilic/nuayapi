<?php
require_once 'config.php';

// Admin tablosunu oluştur
$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Admin tablosu başarıyla oluşturuldu.<br>";
} else {
    echo "Hata: " . $conn->error . "<br>";
}

// Varsayılan admin kullanıcısını ekle
$sql = "INSERT INTO admin (id, username, password) VALUES 
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username=username";

if ($conn->query($sql) === TRUE) {
    echo "Varsayılan admin kullanıcısı eklendi.<br>";
} else {
    echo "Hata: " . $conn->error . "<br>";
}

$conn->close();
echo "İşlem tamamlandı.";
?> 