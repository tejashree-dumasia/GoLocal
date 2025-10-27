<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get posted data
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$new_username = isset($data->username) ? $data->username : "";

if (!$jwt || !$new_username) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
    return;
}

try {
    include_once __DIR__ . '/../../config/core.php';
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Set user properties for update
    $user->user_id = $user_id;
    $user->username = $new_username;

    // Attempt to update the user
    if ($user->update()) {
        http_response_code(200);
        echo json_encode(array("message" => "User profile updated successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update user profile."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>