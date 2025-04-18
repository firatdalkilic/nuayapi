<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Geçersiz istek yöntemi.');
    }

    // Get property ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) {
        throw new Exception('Geçersiz ilan ID\'si.');
    }

    // Validate required fields
    $requiredFields = ['title', 'property_type', 'price'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception($field . ' alanı zorunludur.');
        }
    }

    // Prepare data for update
    $data = [
        'title' => trim($_POST['title']),
        'property_type' => trim($_POST['property_type']),
        'price' => str_replace(['₺', '.', ','], ['', '', '.'], $_POST['price']), // Convert price format
        'description' => trim($_POST['description'] ?? ''),
        'video_call' => isset($_POST['video_call']) ? 1 : 0,
        'square_meters' => !empty($_POST['square_meters']) ? floatval($_POST['square_meters']) : null,
        'net_area' => !empty($_POST['net_area']) ? floatval($_POST['net_area']) : null,
        'room_count' => trim($_POST['room_count'] ?? ''),
        'building_age' => trim($_POST['building_age'] ?? ''),
        'floor_number' => trim($_POST['floor_number'] ?? ''),
        'heating_type' => trim($_POST['heating_type'] ?? ''),
        'bathroom_count' => trim($_POST['bathroom_count'] ?? ''),
        'balcony' => isset($_POST['balcony']) ? 1 : 0,
        'furnished' => isset($_POST['furnished']) ? 1 : 0,
        'using_status' => trim($_POST['using_status'] ?? ''),
        'dues' => !empty($_POST['dues']) ? floatval(str_replace(['₺', '.', ','], ['', '', '.'], $_POST['dues'])) : null,
        'swap' => isset($_POST['swap']) ? 1 : 0,
        'front' => trim($_POST['front'] ?? ''),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Start transaction
    $pdo->beginTransaction();

    // Update property
    $sql = "UPDATE properties SET ";
    $updateFields = [];
    $params = [];
    foreach ($data as $key => $value) {
        $updateFields[] = "`$key` = ?";
        $params[] = $value;
    }
    $sql .= implode(', ', $updateFields);
    $sql .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Handle image uploads if any
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = '../uploads/properties/';
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $uploadFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $uploadFile)) {
                    // Insert image record
                    $stmt = $pdo->prepare("INSERT INTO property_images (property_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$id, $fileName]);
                }
            }
        }
    }

    // Handle video upload if any
    if (!empty($_FILES['video']['name'])) {
        if ($_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $videoName = uniqid() . '_' . basename($_FILES['video']['name']);
            $uploadFile = '../uploads/videos/' . $videoName;
            
            if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadFile)) {
                // Update video path
                $stmt = $pdo->prepare("UPDATE properties SET video_path = ? WHERE id = ?");
                $stmt->execute([$videoName, $id]);
            }
        }
    }

    // Delete images if requested
    if (!empty($_POST['delete_images'])) {
        $deleteImages = json_decode($_POST['delete_images'], true);
        if (is_array($deleteImages)) {
            foreach ($deleteImages as $imageId) {
                // Get image path
                $stmt = $pdo->prepare("SELECT image_path FROM property_images WHERE id = ? AND property_id = ?");
                $stmt->execute([$imageId, $id]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($image) {
                    // Delete file
                    $filePath = '../uploads/properties/' . $image['image_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                    // Delete record
                    $stmt = $pdo->prepare("DELETE FROM property_images WHERE id = ? AND property_id = ?");
                    $stmt->execute([$imageId, $id]);
                }
            }
        }
    }

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'İlan başarıyla güncellendi.'
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 