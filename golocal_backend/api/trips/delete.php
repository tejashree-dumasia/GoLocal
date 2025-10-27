<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get data from the request
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$trip_id = isset($data->trip_id) ? $data->trip_id : "";

if (!$jwt || !$trip_id) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
    return;
}

try {
    include_once __DIR__ . '/../../config/core.php';
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $logged_in_user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // 1. Verify the user is the main admin of this trip
    // We only allow the main admin to delete the entire trip
    $query = "SELECT admin_id FROM trips 
              WHERE trip_id = :trip_id AND admin_id = :admin_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':admin_id', $logged_in_user_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // If no row is found, the user is not the admin
        http_response_code(403); // Forbidden
        echo json_encode(array("message" => "Access denied. Only the trip admin can delete this trip."));
        return;
    }

    // 2. If admin check passes, delete the trip
    // Because of 'ON DELETE CASCADE' in your database, this will also delete
    // all related participants, checklist items, photos, and chat messages.
    $query = "DELETE FROM trips WHERE trip_id = :trip_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Trip successfully deleted."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete trip."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>