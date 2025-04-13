<?php
session_start();

// Oturum kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Mevcut şifreyi kontrol et
    $query = "SELECT password FROM admin WHERE id = 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];

        if (password_verify($current_password, $stored_password)) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    // Yeni şifreyi hashle ve güncelle
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE admin SET password = '$hashed_password' WHERE id = 1";
                    
                    if ($conn->query($update_query)) {
                        $_SESSION['success'] = "Şifreniz başarıyla güncellendi.";
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $error_message = "Şifre güncellenirken bir hata oluştu: " . $conn->error;
                    }
                } else {
                    $error_message = "Yeni şifre en az 6 karakter uzunluğunda olmalıdır.";
                }
            } else {
                $error_message = "Yeni şifre ve şifre tekrarı eşleşmiyor.";
            }
        } else {
            $error_message = "Mevcut şifre yanlış.";
        }
    } else {
        $error_message = "Admin kullanıcısı bulunamadı.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - Admin Panel</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <style>
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 100;
            background: rgba(37, 99, 235, 0.9);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .back-button:hover {
            background: rgba(29, 78, 216, 0.95);
            color: white;
            transform: translateX(-3px);
        }
    </style>
</head>
<body class="admin-dashboard">
    <a href="dashboard.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Şifre Değiştir</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mevcut Şifre</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Yeni Şifre</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">Şifre en az 6 karakter uzunluğunda olmalıdır.</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Şifreyi Değiştir</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> 