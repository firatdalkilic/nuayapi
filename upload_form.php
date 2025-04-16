<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photo"])) {
    $target_dir = "admin/uploads/agents/";
    
    // Klasörü oluştur
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $agent = $_POST["agent"];
    $file = $_FILES["photo"];
    $target_file = $target_dir . ($agent == "firat" ? "firat.jpg" : "aysenur.jpg");
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        echo "Fotoğraf başarıyla yüklendi.";
    } else {
        echo "Fotoğraf yüklenirken hata oluştu.";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Danışman Fotoğrafı Yükle</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select, input[type="file"] { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <h2>Danışman Fotoğrafı Yükle</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="agent">Danışman:</label>
            <select name="agent" id="agent" required>
                <option value="firat">Fırat Dalkılıç</option>
                <option value="aysenur">Ayşenur Eker</option>
            </select>
        </div>
        <div class="form-group">
            <label for="photo">Fotoğraf (JPG):</label>
            <input type="file" name="photo" id="photo" accept=".jpg,.jpeg" required>
        </div>
        <button type="submit">Yükle</button>
    </form>
</body>
</html> 