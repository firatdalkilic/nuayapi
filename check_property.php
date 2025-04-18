<?php
require_once 'admin/config.php';

$id = 34;
$sql = "SELECT id, title, square_meters, property_type FROM properties WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "İlan ID: " . $row['id'] . "\n";
    echo "Başlık: " . $row['title'] . "\n";
    echo "Alan (m²): " . ($row['square_meters'] ?? 'NULL') . "\n";
    echo "Emlak Tipi: " . $row['property_type'] . "\n";
} else {
    echo "İlan bulunamadı.";
} 