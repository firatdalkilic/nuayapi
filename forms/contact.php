<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al ve temizle
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = strip_tags(trim($_POST["message"]));

    // Hata kontrolü
    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Lütfen tüm alanları doğru şekilde doldurun.";
        exit;
    }

    try {
        $mail = new PHPMailer(true);

        // SMTP ayarları
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'firatdalkilic87@gmail.com'; // Gmail adresinizi yazın
        $mail->Password = 'ghpv zwmh ibwe ahme'; // Gmail uygulama şifrenizi yazın
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Alıcı ayarları
        $mail->setFrom($email, $name);
        $mail->addAddress('firatdalkilic87@gmail.com', 'Fırat Dalkılıç');
        $mail->addReplyTo($email, $name);

        // İçerik
        $mail->isHTML(true);
        $mail->Subject = "Yeni İletişim Formu Mesajı: " . $subject;
        
        $mail_content = "
        <h3>Yeni İletişim Formu Mesajı</h3>
        <p><strong>İsim:</strong> {$name}</p>
        <p><strong>E-posta:</strong> {$email}</p>
        <p><strong>Konu:</strong> {$subject}</p>
        <p><strong>Mesaj:</strong><br>{$message}</p>
        ";
        
        $mail->Body = $mail_content;
        $mail->AltBody = strip_tags($mail_content);

        if ($mail->send()) {
            // Log dosyasına da kaydet
            $log_content = "Tarih: " . date('Y-m-d H:i:s') . "\n";
            $log_content .= "İsim: " . $name . "\n";
            $log_content .= "E-posta: " . $email . "\n";
            $log_content .= "Konu: " . $subject . "\n";
            $log_content .= "Mesaj: " . $message . "\n";
            $log_content .= "E-posta Durumu: Gönderildi\n";
            $log_content .= "----------------------------------------\n";

            $log_file = __DIR__ . '/contact_messages.log';
            file_put_contents($log_file, $log_content, FILE_APPEND);

            http_response_code(200);
            echo "OK";
        } else {
            throw new Exception('E-posta gönderilemedi.');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "Üzgünüz, bir hata oluştu: " . $e->getMessage();
    }
} else {
    http_response_code(403);
    echo "Form gönderiminde bir sorun oluştu.";
    exit;
} 