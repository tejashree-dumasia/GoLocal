<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get posted data
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";

if (!$jwt) {
    http_response_code(400);
    echo json_encode(array("message" => "JWT not provided."));
    return;
}

try {
    include_once __DIR__ . '/../../config/core.php';
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Set user ID for deletion
    $user->user_id = $user_id;

    // Attempt to delete the user
    if ($user->delete()) {
        http_response_code(200);
        echo json_encode(array("message" => "User account deleted successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete user account."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>