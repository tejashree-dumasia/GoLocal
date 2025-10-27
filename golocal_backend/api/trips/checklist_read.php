<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get request headers
$headers = apache_request_headers();
$jwt = null;

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1];
}

// Get the trip ID from the URL query string
$trip_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Trip ID not specified.")));

if (!$jwt) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. No token provided."));
    return;
}

try {
    // --- JWT VALIDATION ---
    // $secret_key is loaded from config/core.php in index.php
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $logged_in_user_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // --- SECURITY CHECK ---
    // First, verify the user is part of this trip (admin, co-admin, or participant)
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

    // --- FETCH CHECKLIST DATA ---
    $checklist = new Checklist($db);
    $checklist->trip_id = $trip_id;

    $stmt = $checklist->read();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $items_arr = array();
        $items_arr["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $item = array(
                "item_id" => $item_id,
                "item_description" => $item_description,
                "is_completed" => (bool)$is_completed, // Cast to boolean
                "created_by_user_id" => $created_by_user_id,
                "created_by_username" => $created_by_username
            );
            array_push($items_arr["records"], $item);
        }

        http_response_code(200);
        echo json_encode($items_arr);
    } else {
        http_response_code(200);
        echo json_encode(array("records" => array(), "message" => "Checklist is empty."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
    ));
}
?>