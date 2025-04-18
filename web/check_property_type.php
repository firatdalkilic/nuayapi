<?php
require_once '../admin/config.php';

try {
    $sql = "SELECT id, property_type, LENGTH(property_type) as type_length, 
            net_area, square_meters 
            FROM properties 
            WHERE property_type LIKE '%İş%' OR property_type LIKE '%is%'";
    
    $result = $conn->query($sql);
    
    echo "Property Type Kontrol:<br><br>";
    
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "<br>";
        echo "Property Type: '" . $row['property_type'] . "'<br>";
        echo "Type Length: " . $row['type_length'] . "<br>";
        echo "Net Area: " . ($row['net_area'] ?? 'NULL') . "<br>";
        echo "Square Meters: " . ($row['square_meters'] ?? 'NULL') . "<br>";
        echo "ASCII Values: ";
        for ($i = 0; $i < strlen($row['property_type']); $i++) {
            echo ord($row['property_type'][$i]) . " ";
        }
        echo "<br><br>";
    }
    
} catch (Exception $e) {
    echo "Bir hata oluştu: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 