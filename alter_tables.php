<?php
require_once 'admin/config.php';

// Select the database
mysqli_select_db($conn, $dbname);

$alterQueries = [
    "ALTER TABLE properties ADD COLUMN room_count VARCHAR(50) DEFAULT NULL AFTER net_area",
    "ALTER TABLE properties ADD COLUMN living_room_count VARCHAR(50) DEFAULT NULL AFTER room_count"
];

foreach ($alterQueries as $query) {
    if ($conn->query($query)) {
        echo "Success: " . $query . "\n";
    } else {
        echo "Error: " . $query . "\n";
        echo "MySQL Error: " . $conn->error . "\n";
    }
}

echo "All operations completed.\n";
$conn->close();
?> 