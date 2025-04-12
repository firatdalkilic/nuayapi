<?php
session_start();
require_once 'config.php';
checkLogin();

// Salon sayısı null olan ilanlara 0 değerini ata
$sql = "UPDATE properties SET living_room = 0 WHERE living_room IS NULL";
$stmt = $conn->prepare($sql);
if ($stmt->execute()) {
    $_SESSION['success'] = "Salon sayıları başarıyla güncellendi.";
} else {
    $_SESSION['error'] = "Salon sayıları güncellenirken bir hata oluştu.";
}

header("Location: dashboard.php");
exit;
?> 