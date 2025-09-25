<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get data from the request
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$participant_id = isset($data->participant_id) ? $data->participant_id : "";

if (!$jwt || !$participant_id) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
    return;
}

try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $admin_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        http_response_code(500);
        echo json_encode(array("message" => "Database connection error."));
        return;
    }
    // First, get the trip_id from the participant_id to verify admin status
    $query = "SELECT trip_id FROM trip_participants WHERE participant_id = :participant_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':participant_id', $participant_id);
    $stmt->execute();
    
    if($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "Participant not found."));
        return;
    }
    
    $participant_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $trip_id = $participant_row['trip_id'];

    // Verify the user is an admin or co-admin of this trip
    $query = "SELECT trip_id FROM trips WHERE trip_id = :trip_id AND (admin_id = :admin_id OR co_admin_id = :admin_id)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':admin_id', $admin_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. You are not an admin for this trip."));
        return;
    }

    // If all checks pass, proceed to delete the participant
    $query = "DELETE FROM trip_participants WHERE participant_id = :participant_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':participant_id', $participant_id);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to prepare statement."));
        return;
    }

    if ($stmt->execute()) {        
        http_response_code(200);
        echo json_encode(array("message" => "Participant removed successfully."));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to remove participant."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>