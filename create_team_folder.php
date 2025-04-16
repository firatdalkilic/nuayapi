<?php
// Hedef klasör
$team_dir = "assets/img/team";

// Klasörün varlığını kontrol et
if (!is_dir($team_dir)) {
    if (!mkdir($team_dir, 0755, true)) {
        die("Klasör oluşturulamadı: " . $team_dir);
    }
    echo "Team klasörü oluşturuldu\n";
}

echo "Klasörler hazır\n";
?> 