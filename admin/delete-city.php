<?php
session_start();
require_once 'config.php';
checkLogin();

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Geçersiz şehir ID'si.";
    header('Location: dashboard.php');
    exit;
}

$city_id = (int)$_GET['id'];

// İşlemi bir transaction içinde yapalım
$conn->begin_transaction();

try {
    // Önce bu şehre bağlı tüm properties kayıtlarını bulalım
    $properties_stmt = $conn->prepare("SELECT id FROM properties WHERE city_id = ?");
    $properties_stmt->bind_param("i", $city_id);
    $properties_stmt->execute();
    $properties_result = $properties_stmt->get_result();
    
    while ($property = $properties_result->fetch_assoc()) {
        $property_id = $property['id'];
        
        // Her bir property için resimleri silelim
        $img_stmt = $conn->prepare("SELECT image_name FROM property_images WHERE property_id = ?");
        $img_stmt->bind_param("i", $property_id);
        $img_stmt->execute();
        $images_result = $img_stmt->get_result();
        
        // Fiziksel resimleri silelim
        while ($image = $images_result->fetch_assoc()) {
            $image_path = "../uploads/" . $image['image_name'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Property'ye ait resimleri veritabanından silelim
        $delete_images = $conn->prepare("DELETE FROM property_images WHERE property_id = ?");
        $delete_images->bind_param("i", $property_id);
        $delete_images->execute();
    }
    
    // Şehre ait tüm properties kayıtlarını silelim
    $delete_properties = $conn->prepare("DELETE FROM properties WHERE city_id = ?");
    $delete_properties->bind_param("i", $city_id);
    $delete_properties->execute();
    
    // Son olarak şehri silelim
    $delete_city = $conn->prepare("DELETE FROM cities WHERE id = ?");
    $delete_city->bind_param("i", $city_id);
    $delete_city->execute();
    
    // İşlemi onaylayalım
    $conn->commit();
    
    $_SESSION['success'] = "Şehir ve ilgili tüm kayıtlar başarıyla silindi.";
} catch (Exception $e) {
    // Hata durumunda işlemi geri alalım
    $conn->rollback();
    $_SESSION['error'] = "Şehir silinirken bir hata oluştu: " . $e->getMessage();
}

header('Location: dashboard.php');
exit; 