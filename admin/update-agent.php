<?php
require_once 'config.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = $_POST['agent_id'] ?? 0;
    $agent_name = $_POST['agent_name'] ?? '';
    $agent_title = $_POST['agent_title'] ?? '';
    $agent_phone = $_POST['agent_phone'] ?? '';
    $agent_email = $_POST['agent_email'] ?? '';
    $agent_photo = $_POST['agent_photo'] ?? '';
    $agent_bio = $_POST['agent_bio'] ?? '';
    $sahibinden_store = $_POST['sahibinden_store'] ?? '';
    $emlakjet_profile = $_POST['emlakjet_profile'] ?? '';
    $facebook_username = $_POST['facebook_username'] ?? '';

    $stmt = $conn->prepare("UPDATE agents SET 
        agent_name = ?,
        agent_title = ?,
        agent_phone = ?,
        agent_email = ?,
        agent_photo = ?,
        agent_bio = ?,
        sahibinden_store = ?,
        emlakjet_profile = ?,
        facebook_username = ?
        WHERE id = ?");

    $stmt->bind_param("sssssssssi", 
        $agent_name,
        $agent_title,
        $agent_phone,
        $agent_email,
        $agent_photo,
        $agent_bio,
        $sahibinden_store,
        $emlakjet_profile,
        $facebook_username,
        $agent_id
    );

    if ($stmt->execute()) {
        header("Location: manage-agents.php?success=1");
        exit;
    } else {
        header("Location: manage-agents.php?error=1");
        exit;
    }
}

header("Location: manage-agents.php");
exit;
?> 