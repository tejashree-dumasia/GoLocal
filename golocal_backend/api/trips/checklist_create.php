<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get posted data
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$trip_id = isset($data->trip_id) ? $data->trip_id : "";
$item_description = isset($data->item_description) ? $data->item_description : "";

if (!$jwt || !$trip_id || empty($item_description)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data. Trip ID and item description are required."));
    return;
}

try {
    // --- JWT VALIDATION ---
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $logged_in_user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // --- SECURITY CHECK ---
    // (This is the same security check from checklist_read.php)
    // We must verify the user is part of this trip to add items.
    $security_query = "SELECT t.admin_id FROM trips t
                       LEFT JOIN trip_participants tp ON t.trip_id = tp.trip_id
                       WHERE t.trip_id = :trip_id 
                       AND (t.admin_id = :user_id OR t.co_admin_id = :user_id OR tp.user_id = :user_id)";
    
    $stmt = $db->prepare($security_query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':user_id', $logged_in_user_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(403); // Forbidden
        echo json_encode(array("message" => "Access denied. You are not a participant of this trip."));
        return;
    }

    // --- CREATE CHECKLIST ITEM ---
    $checklist = new Checklist($db);
    $checklist->trip_id = $trip_id;
    $checklist->item_description = $item_description;
    $checklist->created_by_user_id = $logged_in_user_id;

    if ($checklist->create()) {
        http_response_code(201); // 201 Created
        echo json_encode(array("message" => "Checklist item added."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to add item."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
    ));
}
?>