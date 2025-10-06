<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get data from the request
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$trip_id = isset($data->trip_id) ? $data->trip_id : "";
$new_coadmin_user_id = isset($data->user_id) ? $data->user_id : "";

if (!$jwt || !$trip_id || !$new_coadmin_user_id) {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete data."]);
    return;
}

try {
    include_once __DIR__ . '/../../config/core.php';
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $requester_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // Check if requester is the admin of the trip
    $query = "SELECT admin_id FROM trips WHERE trip_id = :trip_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->execute();
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$trip || $trip['admin_id'] != $requester_id) {
        http_response_code(403);
        echo json_encode(["message" => "Access denied. Only the admin can set the co-admin."]);
        return;
    }

    // Check if the user to be set as co-admin is a participant in this trip
    $query = "SELECT user_id FROM trip_participants WHERE trip_id = :trip_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':user_id', $new_coadmin_user_id);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(["message" => "User is not a participant in this trip."]);
        return;
    }

    // Update the co_admin_id in the trips table
    $query = "UPDATE trips SET co_admin_id = :co_admin_id WHERE trip_id = :trip_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':co_admin_id', $new_coadmin_user_id);
    $stmt->bindParam(':trip_id', $trip_id);
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Co-admin updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to update co-admin."]);
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Access denied.", "error" => $e->getMessage()]);
}
