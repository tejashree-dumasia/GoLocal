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

// If a JWT is present, proceed with validation
if ($jwt) {
    try {
        // --- JWT VALIDATION ---
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        $logged_in_user_id = $decoded->data->id; // Extract user ID from token

        // --- FETCH TRIPS FROM DATABASE ---
        $database = new Database();
        $db = $database->getConnection();
        $trip = new Trip($db);

        // Query trips for the logged-in user
        $stmt = $trip->read($logged_in_user_id);
        $num = $stmt->rowCount();

        // Check if any trips were found
        if ($num > 0) {
            $trips_arr = array();
            $trips_arr["records"] = array();

            // Retrieve the table contents
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $trip_item = array(
                    "trip_id" => $trip_id,
                    "trip_name" => $trip_name,
                    "location" => $location,
                    "description" => html_entity_decode($description),
                    "estimated_cost" => $estimated_cost,
                    "start_datetime" => $start_datetime,
                    "end_datetime" => $end_datetime,
                    "admin_id" => $admin_id
                );

                array_push($trips_arr["records"], $trip_item);
            }

            // Set response code - 200 OK
            http_response_code(200);
            echo json_encode($trips_arr);
        } else {
            // No trips found for this user
            http_response_code(200); // 200 OK is appropriate, the request was valid
            echo json_encode(
                array("records" => array(), "message" => "No trips found for this user.")
            );
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