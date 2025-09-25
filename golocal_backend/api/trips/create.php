<?php
// Note: This file is called by `public/index.php`, which handles core includes.
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get the posted data and request headers
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

        // --- GET DATA FROM REQUEST ---
        $data = json_decode(file_get_contents("php://input"));

        // Validate the incoming data
        if (
            !empty($data->trip_name) &&
            !empty($data->location) &&
            !empty($data->start_datetime) &&
            !empty($data->end_datetime)
        ) {
            // Get database connection
            $database = new Database();
            $db = $database->getConnection();
            $trip = new Trip($db);

            // Set trip property values from the request data
            $trip->trip_name = $data->trip_name;
            $trip->location = $data->location;
            $trip->start_datetime = $data->start_datetime;
            $trip->end_datetime = $data->end_datetime;
            
            // Set optional properties
            $trip->description = !empty($data->description) ? $data->description : null;
            $trip->estimated_cost = !empty($data->estimated_cost) ? $data->estimated_cost : null;
            
            // Set the admin_id from the logged-in user's token
            $trip->admin_id = $logged_in_user_id;

            // Attempt to create the trip
            if ($trip->create()) {
                // Set response code - 201 Created
                http_response_code(201);
                echo json_encode(array("message" => "Trip was successfully created."));
            } else {
                // Set response code - 503 Service Unavailable
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create trip."));
            }
        } else {
            // Set response code - 400 Bad Request
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create trip. Data is incomplete."));
        }
    } catch (Exception $e) {
        // This will happen if the token is expired or invalid
        // Set response code - 401 Unauthorized
        http_response_code(401);
        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
} else {
    // If no JWT was provided
    // Set response code - 401 Unauthorized
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. No token provided."));
}
?>