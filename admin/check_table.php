<?php
require_once 'config.php';

$query = "DESCRIBE properties";
$result = $conn->query($query);

if ($result) {
    echo "Tablo yapısı:<br><br>";
    while ($row = $result->fetch_assoc()) {
        echo "Sütun: " . $row['Field'] . "<br>";
        echo "Tip: " . $row['Type'] . "<br>";
        echo "Null?: " . $row['Null'] . "<br>";
        echo "Varsayılan: " . $row['Default'] . "<br>";
        echo "------------------------<br>";
    }
} else {
    echo "Tablo yapısı alınamadı: " . $conn->error;
}

$conn->close();
?> 