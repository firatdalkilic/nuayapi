<?php
// Oturum kontrolü fonksiyonu
function checkLogin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: login.php");
        exit;
    }
}

// Fonksiyonu çağır
checkLogin();
?> 