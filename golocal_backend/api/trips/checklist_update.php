<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get posted data
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$item_id = isset($data->item_id) ? $data->item_id : "";
$is_completed = isset($data->is_completed) ? $data->is_completed : null;

if (!$jwt || !$item_id || $is_completed === null) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data. Item ID and completion status are required."));
    return;
}

try {
    // --- JWT VALIDATION ---
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $logged_in_user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // --- SECURITY CHECK ---
    // We must verify the user is part of this trip to update items.
    // First, find the trip_id from the item_id
    $trip_id_query = "SELECT trip_id FROM checklists WHERE item_id = :item_id";
    $stmt = $db->prepare($trip_id_query);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->execute();
    
    if($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "Checklist item not found."));
        return;
    }
    $trip_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $trip_id = $trip_row['trip_id'];

    // Now, verify the user is a participant of that trip
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

    // --- UPDATE CHECKLIST ITEM ---
    $checklist = new Checklist($db);
    $checklist->item_id = $item_id;
    $checklist->is_completed = $is_completed ? 1 : 0; // Convert boolean to 1 or 0 for database

    if ($checklist->updateStatus()) {
        http_response_code(200);
        echo json_encode(array("message" => "Checklist item updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update item."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
    ));
}
?>