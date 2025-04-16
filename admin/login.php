<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Debug bilgilerini logla
    error_log("Login attempt details:");
    error_log("Username: " . $username);
    error_log("Raw password length: " . strlen($password));
    
    // Önce agents tablosunda kontrol et
    $sql = "SELECT * FROM agents WHERE username_panel = ?";
    error_log("SQL Query: " . $sql);
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    error_log("Query executed, found rows: " . $result->num_rows);
    
    if ($result->num_rows > 0) {
        $agent = $result->fetch_assoc();
        error_log("Agent found:");
        error_log("ID: " . $agent['id']);
        error_log("Agent name: " . $agent['agent_name']);
        error_log("Stored hash info: " . substr($agent['password'], 0, 7) . "...");
        error_log("Stored hash length: " . strlen($agent['password']));
        
        // Test hash oluştur
        $test_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        error_log("Test hash info: " . substr($test_hash, 0, 7) . "...");
        
        // Şifre doğrulamasını test et
        $verify_result = password_verify($password, $agent['password']);
        error_log("Password verification result: " . ($verify_result ? "PASSED" : "FAILED"));
        
        if ($verify_result) {
            error_log("Login successful for agent: " . $agent['agent_name']);
            $_SESSION['agent_logged_in'] = true;
            $_SESSION['agent_id'] = $agent['id'];
            $_SESSION['agent_name'] = $agent['agent_name'];
            header("Location: dashboard.php");
            exit;
        } else {
            error_log("Password verification failed");
            error_log("Raw password hash comparison:");
            error_log("Input: " . substr(password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]), 0, 7) . "...");
            error_log("Stored: " . substr($agent['password'], 0, 7) . "...");
        }
    } else {
        error_log("No agent found with username: " . $username);
    }
    
    // Danışman girişi başarısız, admin girişini kontrol et
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        error_log("Admin login successful");
        $_SESSION['admin_logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    }
    
    error_log("Login failed for username: " . $username);
    $error = "Geçersiz kullanıcı adı veya şifre!";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Nua Yapı Admin</title>
    <link href="../assets/img/nua_logo.jpg" rel="icon">
    <link href="../assets/img/nua_logo.jpg" rel="apple-touch-icon">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 150px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo-container">
                <img src="../assets/img/nua_logo.jpg" alt="Nua Logo">
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Kullanıcı Adı</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
            </form>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> 