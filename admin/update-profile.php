<?php
require_once 'session_manager.php';
require_once 'config.php';
require_once 'check_login.php';

// Giriş kontrolü
checkLogin();

// Sadece danışmanlar erişebilir
if (!isAgent()) {
    $_SESSION['error'] = "Bu sayfaya erişim yetkiniz yok.";
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    try {
        $agent_id = $_SESSION['agent_id'];
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $about = trim($_POST['about']);
        $sahibinden_link = trim($_POST['sahibinden_link']);
        $emlakjet_link = trim($_POST['emlakjet_link']);
        $facebook_link = trim($_POST['facebook_link']);

        // Temel SQL sorgusu
        $sql = "UPDATE agents SET 
                agent_name=?, 
                username=?, 
                phone=?, 
                email=?, 
                about=?,
                sahibinden_link=?,
                emlakjet_link=?,
                facebook_link=?";
        
        $types = "ssssssss";
        $params = array($name, $username, $phone, $email, $about, $sahibinden_link, $emlakjet_link, $facebook_link);

        // Resim yükleme işlemi
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../uploads/agents/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $image = "agent_" . time() . "." . $imageFileType;
            $target_file = $target_dir . $image;

            // Resim yükleme
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = "uploads/agents/" . $image;
                $sql .= ", image=?";
                $types .= "s";
                $params[] = $image;
            }
        }

        // WHERE koşulu
        $sql .= " WHERE id=?";
        $types .= "i";
        $params[] = $agent_id;

        // Sorguyu hazırla ve çalıştır
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            // Session'daki danışman adını güncelle
            $_SESSION['agent_name'] = $name;
            $_SESSION['success'] = "Profiliniz başarıyla güncellendi.";
        } else {
            throw new Exception("Güncelleme sırasında bir hata oluştu.");
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: dashboard.php");
exit; 