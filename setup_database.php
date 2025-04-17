<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";

try {
    // Ana bağlantıyı oluştur
    $conn = new mysqli($servername, $username, $password);
    
    // Veritabanını oluştur
    $sql = "CREATE DATABASE IF NOT EXISTS nuayapi";
    if ($conn->query($sql)) {
        echo "Veritabanı oluşturuldu veya zaten mevcut.<br>";
    } else {
        throw new Exception("Veritabanı oluşturma hatası: " . $conn->error);
    }
    
    // Veritabanını seç
    $conn->select_db("nuayapi");
    
    // Agents tablosunu oluştur
    $sql = "CREATE TABLE IF NOT EXISTS agents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        agent_name VARCHAR(255) NOT NULL,
        agent_title VARCHAR(255) DEFAULT 'Gayrimenkul Danışmanı',
        sahibinden_store VARCHAR(255),
        emlakjet_profile VARCHAR(255),
        facebook_username VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "Agents tablosu oluşturuldu veya zaten mevcut.<br>";
    } else {
        throw new Exception("Tablo oluşturma hatası: " . $conn->error);
    }
    
    // Örnek danışman ekle
    $sql = "INSERT INTO agents (agent_name, sahibinden_store, emlakjet_profile, facebook_username) 
            VALUES ('Ahmet Yılmaz', 'nuayapi', 'nua-yapi', 'nuayapi')
            ON DUPLICATE KEY UPDATE 
            sahibinden_store = VALUES(sahibinden_store),
            emlakjet_profile = VALUES(emlakjet_profile),
            facebook_username = VALUES(facebook_username)";
            
    if ($conn->query($sql)) {
        echo "Danışman bilgileri güncellendi.<br>";
    } else {
        throw new Exception("Danışman ekleme/güncelleme hatası: " . $conn->error);
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