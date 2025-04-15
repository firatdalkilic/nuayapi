<?php
require_once 'session_manager.php';

error_log("Check Login: Başlatılıyor");
error_log("Session Status: " . session_status());
error_log("Session ID: " . session_id());

// Oturum kontrolü fonksiyonu
function checkLogin() {
    error_log("checkLogin fonksiyonu çağrıldı");
    error_log("SESSION içeriği: " . print_r($_SESSION, true));
    
    if (!isset($_SESSION['admin_logged_in'])) {
        error_log("admin_logged_in session değişkeni bulunamadı");
        header("Location: login.php");
        exit;
    }
    
    if ($_SESSION['admin_logged_in'] !== true) {
        error_log("admin_logged_in değeri false");
        header("Location: login.php");
        exit;
    }
    
    error_log("Oturum kontrolü başarılı");
}

// Fonksiyonu çağır
checkLogin();
?> 