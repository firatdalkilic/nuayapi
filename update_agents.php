<?php
require_once 'admin/config.php';

try {
    // Fırat'ın fotoğrafını güncelle
    $update_firat = "UPDATE agents SET 
        agent_photo = 'firat.jpg',
        agent_title = 'Gayrimenkul Danışmanı'
        WHERE agent_name = 'Fırat Dalkılıç'";
    
    if ($conn->query($update_firat)) {
        echo "Fırat'ın bilgileri güncellendi\n";
    } else {
        echo "Hata (Fırat): " . $conn->error . "\n";
    }

    // Ayşenur'un fotoğrafını güncelle
    $update_aysenur = "UPDATE agents SET 
        agent_photo = 'aysenur.jpg',
        agent_title = 'Gayrimenkul Danışmanı'
        WHERE agent_name = 'Ayşenur Eker'";
    
    if ($conn->query($update_aysenur)) {
        echo "Ayşenur'un bilgileri güncellendi\n";
    } else {
        echo "Hata (Ayşenur): " . $conn->error . "\n";
    }

    echo "\nGüncel danışman listesi:\n";
    $result = $conn->query("SELECT agent_name, agent_title, agent_photo FROM agents");
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['agent_name'] . " (" . $row['agent_title'] . ")\n";
        echo "  Fotoğraf: " . $row['agent_photo'] . "\n";
    }

} catch (Exception $e) {
    echo "Hata: " . $e->getMessage() . "\n";
}
?> 