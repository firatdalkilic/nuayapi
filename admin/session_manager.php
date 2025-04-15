<?php
error_log("Session Manager: Başlatılıyor");
error_log("Session Status: " . session_status());
error_log("Session ID: " . (session_id() ?: 'Yok'));

if (session_status() === PHP_SESSION_NONE) {
    error_log("Session başlatılıyor...");
    session_start();
    error_log("Session başlatıldı. Yeni Session ID: " . session_id());
} else {
    error_log("Session zaten aktif. Session ID: " . session_id());
}
?> 