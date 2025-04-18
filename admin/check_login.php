<?php
// Session ve functions dosyalarını dahil et
require_once 'session_manager.php';
require_once '../includes/functions.php';

// Debug logları
error_log("Check Login: Başlatılıyor");
error_log("Session Status: " . session_status());
error_log("Session ID: " . session_id());

// Oturum kontrolünü yap
checkLogin();
?> 