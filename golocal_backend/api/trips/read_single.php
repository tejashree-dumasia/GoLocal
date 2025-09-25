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
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

        $logged_in_user_id = $decoded->data->id;
        // --- FETCH TRIP DETAILS FROM DATABASE ---
        $database = new Database();
        $db = $database->getConnection();

        // Prepare the query to get a single trip's details
        // --- UPDATED THIS QUERY to include co_admin_id ---
        $query = "SELECT
                    t.trip_id, t.trip_name, t.location, t.description,
                    t.estimated_cost, t.start_datetime, t.end_datetime,
                    t.admin_id, t.co_admin_id, u.username as admin_name
                FROM
                    trips t
                    LEFT JOIN users u ON t.admin_id = u.user_id
                WHERE
                    t.trip_id = :trip_id
                LIMIT 0,1";
        
        $stmt = $db->prepare($query);
        $trip_id = htmlspecialchars(strip_tags($trip_id));
        $stmt->bindParam(':trip_id', $trip_id);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // This logic will now work correctly
            $isAdmin = ($row['admin_id'] == $logged_in_user_id);
            $isCoAdmin = ($row['co_admin_id'] == $logged_in_user_id);
            $row['is_admin'] = ($isAdmin || $isCoAdmin);

            http_response_code(200);
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Trip not found."));
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