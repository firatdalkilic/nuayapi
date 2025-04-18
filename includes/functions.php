<?php
/**
 * Güvenlik için input temizleme fonksiyonu
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Oturum kontrolü fonksiyonu
 */
function checkLogin() {
    error_log("checkLogin fonksiyonu çağrıldı");
    error_log("SESSION içeriği: " . print_r($_SESSION, true));
    
    // Admin kontrolü
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }
    
    // Danışman kontrolü
    if (isset($_SESSION['agent_logged_in']) && $_SESSION['agent_logged_in'] === true) {
        return true;
    }

    // Giriş yapılmamış, login sayfasına yönlendir
    header("Location: login.php");
    exit;
}

/**
 * Admin kontrolü fonksiyonu
 */
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Danışman kontrolü fonksiyonu
 */
function isAgent() {
    return isset($_SESSION['agent_logged_in']) && $_SESSION['agent_logged_in'] === true;
}

/**
 * Agent ID'sini döndüren fonksiyon
 */
function getAgentId() {
    return isset($_SESSION['agent_id']) ? $_SESSION['agent_id'] : null;
}

/**
 * Fiyat formatını düzenleyen fonksiyon
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.');
}

/**
 * Tarih formatını düzenleyen fonksiyon
 */
function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}

/**
 * Dosya yükleme fonksiyonu
 */
function uploadFile($file, $targetDir) {
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}

/**
 * Resim boyutlandırma fonksiyonu
 */
function resizeImage($sourcePath, $targetPath, $maxWidth, $maxHeight) {
    list($width, $height) = getimagesize($sourcePath);
    
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = $width * $ratio;
    $newHeight = $height * $ratio;
    
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    $source = imagecreatefromjpeg($sourcePath);
    
    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    imagejpeg($thumb, $targetPath, 80);
}

/**
 * Hata mesajı oluşturma fonksiyonu
 */
function createErrorMessage($message) {
    $_SESSION['error'] = $message;
}

/**
 * Başarı mesajı oluşturma fonksiyonu
 */
function createSuccessMessage($message) {
    $_SESSION['success'] = $message;
}

/**
 * Yönlendirme fonksiyonu
 */
function redirect($url) {
    header("Location: $url");
    exit;
} 