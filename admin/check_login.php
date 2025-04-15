<?php
require_once 'session_manager.php';

error_log("Check Login: Başlatılıyor");
error_log("Session Status: " . session_status());
error_log("Session ID: " . session_id());

// Oturum kontrolü fonksiyonu
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

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function isAgent() {
    return isset($_SESSION['agent_logged_in']) && $_SESSION['agent_logged_in'] === true;
}

function getAgentId() {
    return isset($_SESSION['agent_id']) ? $_SESSION['agent_id'] : null;
}

// Fonksiyonu çağır
checkLogin();
?> 