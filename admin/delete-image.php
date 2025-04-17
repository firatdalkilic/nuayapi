<?php
session_start();
require_once 'config.php';
require_once 'check_login.php';
checkLogin();

if (!isset($_GET['id']) || !isset($_GET['property_id'])) {
    $_SESSION['error'] = "Geçersiz resim ID'si.";
    header('Location: dashboard.php');
    exit;
}

$image_id = (int)$_GET['id'];
$property_id = (int)$_GET['property_id'];

// Resim bilgilerini al
$stmt = $conn->prepare("SELECT image_name FROM property_images WHERE id = ? AND property_id = ?");
$stmt->bind_param("ii", $image_id, $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $image = $result->fetch_assoc();
    $image_path = dirname(__DIR__) . "/uploads/" . $image['image_name'];
    
    // Dosyayı sil
    if (file_exists($image_path)) {
        unlink($image_path);
    }
    
    // Veritabanından sil
    $delete_stmt = $conn->prepare("DELETE FROM property_images WHERE id = ? AND property_id = ?");
    $delete_stmt->bind_param("ii", $image_id, $property_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Resim başarıyla silindi.";
    } else {
        $_SESSION['error'] = "Resim silinirken bir hata oluştu.";
    }
} else {
    $_SESSION['error'] = "Resim bulunamadı.";
}

header("Location: edit-property.php?id=" . $property_id);
exit; 