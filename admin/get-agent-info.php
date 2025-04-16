<?php
require_once 'session_manager.php';
require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// Sadece danışmanlar erişebilir
if (!isAgent()) {
    http_response_code(403);
    echo json_encode(['error' => 'Yetkisiz erişim']);
    exit;
}

// Danışman bilgilerini getir
$agent_id = $_SESSION['agent_id'];
$sql = "SELECT agent_name, username, phone, email, about, image, sahibinden_link, emlakjet_link, facebook_link 
        FROM agents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();

if ($agent = $result->fetch_assoc()) {
    // Hassas bilgileri temizle
    unset($agent['password']);
    
    // JSON olarak döndür
    header('Content-Type: application/json');
    echo json_encode($agent);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Danışman bulunamadı']);
} 