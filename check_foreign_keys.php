<?php
require_once 'admin/config.php';

echo "\nForeign Key Constraints:\n";
echo "=======================\n\n";

try {
    $query = "SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                DELETE_RULE
              FROM information_schema.REFERENTIAL_CONSTRAINTS 
              WHERE CONSTRAINT_SCHEMA = DATABASE()";
              
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "Table: " . $row['TABLE_NAME'] . "\n";
            echo "Constraint: " . $row['CONSTRAINT_NAME'] . "\n";
            echo "Referenced Table: " . $row['REFERENCED_TABLE_NAME'] . "\n";
            echo "Delete Rule: " . $row['DELETE_RULE'] . "\n";
            echo "------------------------\n";
        }
        $result->free();
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?> 