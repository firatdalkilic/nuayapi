<?php
$url = getenv('JAWSDB_URL');

if (!$url) {
    die("JAWSDB_URL environment variable is not set");
}

$dbparts = parse_url($url);

if (!$dbparts || !isset($dbparts['host']) || !isset($dbparts['user']) || !isset($dbparts['pass']) || !isset($dbparts['path'])) {
    die("Invalid JAWSDB_URL format");
}

$hostname = $dbparts['host'];
$username = $dbparts['user'];
$password = $dbparts['pass'];
$database = ltrim($dbparts['path'],'/');

echo "Connecting to database...\n";
echo "Host: " . $hostname . "\n";
echo "Database: " . $database . "\n";
echo "Username: " . $username . "\n";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully\n";

// Run the migration script
require_once('add_features_column.php');

$conn->close();
echo "Connection closed\n";
?> 