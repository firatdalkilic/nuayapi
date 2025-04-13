<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Admin kullanıcısının şifresini kontrol et
$sql = "SELECT * FROM admin WHERE id = 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Kullanıcı adı: " . $row['username'] . "<br>";
    echo "Şifre hash: " . $row['password'] . "<br>";
    
    // Test şifresi ile kontrol
    $test_password = "admin123";
    if (password_verify($test_password, $row['password'])) {
        echo "Test başarılı: 'admin123' şifresi doğru hash ile eşleşiyor.<br>";
    } else {
        echo "Test başarısız: 'admin123' şifresi hash ile eşleşmiyor.<br>";
        
        // Yeni bir hash oluştur
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "Yeni oluşturulan hash: " . $new_hash . "<br>";
    }
} else {
    echo "Admin kullanıcısı bulunamadı.";
}

$conn->close();
?> 