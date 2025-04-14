<?php
require_once 'config.php';

try {
    // Show table structure
    $sql = "SHOW CREATE TABLE properties";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Table Structure:\n";
        echo $row['Create Table'] . "\n\n";
    } else {
        echo "Error getting table structure: " . $conn->error . "\n";
    }
    
    // Show columns
    $sql = "SHOW COLUMNS FROM properties";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "Columns in properties table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "Field: " . $row['Field'] . "\n";
            echo "Type: " . $row['Type'] . "\n";
            echo "Null: " . $row['Null'] . "\n";
            echo "Default: " . $row['Default'] . "\n";
            echo "------------------------\n";
        }
    } else {
        echo "Error getting columns: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 