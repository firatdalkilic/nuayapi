<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Yeni şifre hash'i oluştur
$password = "admin123";
$hash = password_hash($password, PASSWORD_DEFAULT);

// Admin şifresini güncelle
$sql = "UPDATE admin SET password = ? WHERE id = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "Şifre başarıyla güncellendi.<br>";
    echo "Yeni hash: " . $hash . "<br>";
    
    // Doğrulama testi
    if (password_verify($password, $hash)) {
        echo "Doğrulama testi başarılı.";
    } else {
        echo "Doğrulama testi başarısız!";
    }
} else {
    echo "Hata: " . $stmt->error;
}

$stmt->close();
$conn->close();
?> 