<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get posted data
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$item_id = isset($data->item_id) ? $data->item_id : "";

if (!$jwt || !$item_id) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data. Item ID is required."));
    return;
}

try {
    // --- JWT VALIDATION ---
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $logged_in_user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // --- SECURITY CHECK ---
    // Get item creator and trip admins in one query
    $query = "SELECT 
                c.created_by_user_id,
                t.admin_id,
                t.co_admin_id
              FROM checklists c
              JOIN trips t ON c.trip_id = t.trip_id
              WHERE c.item_id = :item_id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "Checklist item not found."));
        return;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user is the creator OR the admin OR the co-admin
    $isCreator = ($row['created_by_user_id'] == $logged_in_user_id);
    $isAdmin = ($row['admin_id'] == $logged_in_user_id);
    $isCoAdmin = ($row['co_admin_id'] == $logged_in_user_id);

    if (!$isCreator && !$isAdmin && !$isCoAdmin) {
        http_response_code(403); // Forbidden
        echo json_encode(array("message" => "Access denied. You are not the item creator or trip admin."));
        return;
    }

    // --- DELETE CHECKLIST ITEM ---
    $checklist = new Checklist($db);
    $checklist->item_id = $item_id;

    if ($checklist->delete()) {
        http_response_code(200);
        echo json_encode(array("message" => "Checklist item deleted."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete item."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
    ));
}
?>