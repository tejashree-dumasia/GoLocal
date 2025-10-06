<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get data from the request
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$trip_id = isset($data->trip_id) ? $data->trip_id : "";

$invitee_email = isset($data->email) ? trim($data->email) : "";

// Validate JWT and that we have an email
if (!$jwt || !$trip_id || empty($invitee_email)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data. Email is required."));
    return;
}

try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $inviter_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // 1. Verify the person inviting is the trip admin (security check)
    $query = "SELECT trip_id FROM trips 
          WHERE trip_id = :trip_id 
          AND (admin_id = :admin_id OR co_admin_id = :admin_id)";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':admin_id', $inviter_id); // The inviter's ID
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. You are not the admin or co-admin of this trip."));
        return;
    }
    
    // --- Logic to Add Participant ---

    // Only allow inviting registered users
    $query = "SELECT user_id, username FROM users WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $invitee_email);
    $stmt->execute();
    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_row) {
        http_response_code(404);
        echo json_encode(array("message" => "No user found with this email. Only registered users can be invited."));
        return;
    }

    $user_id_to_add = $user_row['user_id'];

    // Insert into participants table (only user_id, no guest fields)
    $query = "INSERT INTO trip_participants (trip_id, user_id, status) 
              VALUES (:trip_id, :user_id, 'invited')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':user_id', $user_id_to_add);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Participant successfully added."));
    } else {
        http_response_code(409);
        echo json_encode(array("message" => "Unable to add participant. They may already be in the trip."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>