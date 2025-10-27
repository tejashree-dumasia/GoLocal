<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// --- Get data from POST form ---
// File uploads use multipart/form-data, not JSON
$jwt = isset($_POST['jwt']) ? $_POST['jwt'] : "";
$file = isset($_FILES['profile_picture']) ? $_FILES['profile_picture'] : null;

if (!$jwt || !$file) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data. JWT and file are required."));
    return;
}

// --- File Validation ---
$upload_dir = __DIR__ . '/../../public/uploads/';
$max_file_size = 5000000; // 5MB
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(array("message" => "Error during file upload."));
    return;
}
if ($file['size'] > $max_file_size) {
    http_response_code(400);
    echo json_encode(array("message" => "File is too large. Max 5MB."));
    return;
}
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid file type. Only JPG, PNG, GIF allowed."));
    return;
}

// --- JWT Validation ---
try {
    include_once __DIR__ . '/../../config/core.php';
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $user_id = $decoded->data->id;

    // --- Create a unique filename ---
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $new_filename;

    // --- Move the file ---
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // --- Update database ---
        $database = new Database();
        $db = $database->getConnection();
        
        // We store the relative path from the server root
        $relative_path = 'uploads/' . $new_filename; 
        
        $query = "UPDATE users SET profile_picture_url = :path WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':path', $relative_path);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(array(
                "message" => "Profile picture uploaded.",
                "filePath" => 'http://api.golocal.test/' . $relative_path
            ));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Database update failed."));
        }
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Could not move uploaded file."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>