<?php
session_start();
require_once 'config.php';
checkLogin();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Geçersiz ilan ID'si.";
    header('Location: dashboard.php');
    exit;
}

$property_id = (int)$_GET['id'];

// İşlemi bir transaction içinde yapalım
$conn->begin_transaction();

try {
    // Önce ilanın resimlerini alalım
    $img_stmt = $conn->prepare("SELECT image_name FROM property_images WHERE property_id = ?");
    $img_stmt->bind_param("i", $property_id);
    $img_stmt->execute();
    $result = $img_stmt->get_result();
    
    // Resimleri fiziksel olarak silelim
    while ($row = $result->fetch_assoc()) {
        $image_path = "../uploads/" . $row['image_name'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Resimleri veritabanından silelim
    $delete_images = $conn->prepare("DELETE FROM property_images WHERE property_id = ?");
    $delete_images->bind_param("i", $property_id);
    $delete_images->execute();
    
    // İlanı silelim
    $delete_property = $conn->prepare("DELETE FROM properties WHERE id = ?");
    $delete_property->bind_param("i", $property_id);
    $delete_property->execute();
    
    // İşlemi onaylayalım
    $conn->commit();
    
    $_SESSION['success'] = "İlan ve ilgili tüm resimler başarıyla silindi.";
} catch (Exception $e) {
    // Hata durumunda işlemi geri alalım
    $conn->rollback();
    $_SESSION['error'] = "İlan silinirken bir hata oluştu: " . $e->getMessage();
}

header('Location: dashboard.php');
exit;
?> 