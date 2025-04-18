<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../admin/config.php';

try {
    // Add username_panel column if it doesn't exist
    $checkUsernameColumn = "SHOW COLUMNS FROM `agents` LIKE 'username_panel'";
    $result = $conn->query($checkUsernameColumn);
    if ($result->num_rows === 0) {
        $addUsernameColumn = "ALTER TABLE `agents` ADD COLUMN `username_panel` VARCHAR(50) NOT NULL UNIQUE";
        $conn->query($addUsernameColumn);
        echo "Added username_panel column\n";
    }

    // Add password column if it doesn't exist
    $checkPasswordColumn = "SHOW COLUMNS FROM `agents` LIKE 'password'";
    $result = $conn->query($checkPasswordColumn);
    if ($result->num_rows === 0) {
        $addPasswordColumn = "ALTER TABLE `agents` ADD COLUMN `password` VARCHAR(255) NOT NULL";
        $conn->query($addPasswordColumn);
        echo "Added password column\n";
    }

    // Add is_active column if it doesn't exist
    $checkIsActiveColumn = "SHOW COLUMNS FROM `agents` LIKE 'is_active'";
    $result = $conn->query($checkIsActiveColumn);
    if ($result->num_rows === 0) {
        $addIsActiveColumn = "ALTER TABLE `agents` ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE";
        $conn->query($addIsActiveColumn);
        echo "Added is_active column\n";
    }

    // Set default username and password for agent with ID 1
    $defaultUsername = 'admin';
    $defaultPassword = password_hash('Nua2024!', PASSWORD_DEFAULT);

    $updateStmt = $conn->prepare("UPDATE `agents` SET username_panel = ?, password = ? WHERE id = 1");
    $updateStmt->bind_param("ss", $defaultUsername, $defaultPassword);
    $updateStmt->execute();
    echo "Updated default credentials for agent ID 1\n";

    $updateStmt->close();
    $conn->close();
    echo "Database update completed successfully\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 