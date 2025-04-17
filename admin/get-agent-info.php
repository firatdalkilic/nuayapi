<?php
require_once 'session_manager.php';
require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// Sadece danışmanlar erişebilir
if (!isAgent()) {
    http_response_code(403);
    echo json_encode(['error' => 'Bu sayfaya erişim yetkiniz yok.']);
    exit;
}

try {
    $agent_id = $_SESSION['agent_id'];
    
    // Danışman bilgilerini getir
    $sql = "SELECT agent_name, username_panel as username, phone, email, about, image, sahibinden_link, emlakjet_link, facebook_link FROM agents WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // JSON olarak döndür
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Danışman bulunamadı.']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 