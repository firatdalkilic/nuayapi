<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    try {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            throw new Exception("Tüm alanları doldurun.");
        }

        // Mevcut şifreyi kontrol et
        if ($current_password !== ADMIN_PASSWORD) {
            throw new Exception("Mevcut şifre yanlış.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("Yeni şifre ve şifre tekrarı eşleşmiyor.");
        }

        if (strlen($new_password) < 6) {
            throw new Exception("Yeni şifre en az 6 karakter uzunluğunda olmalıdır.");
        }

        // Heroku config vars'ı güncellemek için API çağrısı yap
        $app_name = 'nuayapi';
        $api_key = getenv('HEROKU_API_KEY');
        
        if (!$api_key) {
            throw new Exception("Heroku API anahtarı bulunamadı. Lütfen sistem yöneticisi ile iletişime geçin.");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.heroku.com/apps/{$app_name}/config-vars");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['ADMIN_PASSWORD' => $new_password]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.heroku+json; version=3',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            throw new Exception("Şifre güncellenirken bir hata oluştu. HTTP Kodu: " . $http_code);
        }

        $_SESSION['success_message'] = "Şifreniz başarıyla güncellendi. Lütfen yeni şifrenizle tekrar giriş yapın.";
        header("Location: logout.php");
        exit;

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Session'dan success mesajını al ve temizle
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(13, 110, 253, 0.9);
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
            background: rgba(11, 94, 215, 0.95);
            transform: scale(1.1);
            color: white;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Şifre Değiştir</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mevcut Şifre</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Yeni Şifre</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">En az 6 karakter</small>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 