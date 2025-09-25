<?php
// Note: This file is called by `public/index.php`, which handles core includes.
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get the request headers
$headers = apache_request_headers();
$jwt = null;

// Check for the Authorization header to get the JWT
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1];
}

// Get the trip ID from the URL query string
$trip_id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(array("message" => "Trip ID not specified.")));

// If a JWT is present, proceed with validation
if ($jwt) {
    try {
        // --- JWT VALIDATION ---
        include_once __DIR__ . '/../../config/core.php';
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

        // --- FETCH PARTICIPANTS FROM DATABASE ---
        $database = new Database();
        $db = $database->getConnection();

        // Prepare the query to get all participants for a given trip
        $query = "SELECT
            tp.participant_id,
            tp.user_id,
            tp.guest_name,
            tp.guest_email,
            tp.status,
            tp.is_co_admin
        FROM
            trip_participants tp
        WHERE
            tp.trip_id = :trip_id";
        
        $stmt = $db->prepare($query);
        
        // Sanitize and bind the trip ID
        $trip_id = htmlspecialchars(strip_tags($trip_id));
        $stmt->bindParam(':trip_id', $trip_id);
        
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $participants_arr = array();
            $participants_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $participant_item = array(
                    "participant_id" => $participant_id,
                    "user_id" => $user_id,
                    "guest_name" => $guest_name,
                    "guest_email" => $guest_email,
                    "status" => $status,
                    "is_co_admin" => $is_co_admin
                );
                array_push($participants_arr["records"], $participant_item);
            }

            // Set response code - 200 OK
            http_response_code(200);
            echo json_encode($participants_arr);
        } else {
            // Set response code - 200 OK, but with an empty list
            http_response_code(200);
            echo json_encode(array("records" => array(), "message" => "No participants found for this trip."));
        }

    } catch (Exception $e) {
        // This will happen if the token is expired or invalid
        http_response_code(401);
        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
} else {
    // If no JWT was provided
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. No token provided."));
}
?>