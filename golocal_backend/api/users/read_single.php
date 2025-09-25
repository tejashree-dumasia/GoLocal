<?php
// This file is called by `public/index.php`, which handles core includes.
// We need to use the JWT library.
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get the posted data and request headers
$headers = apache_request_headers();
$jwt = null;

// Check for the Authorization header
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    // The header is in the format "Bearer {token}"
    $arr = explode(" ", $authHeader);
    $jwt = $arr[1];
}

// Check if JWT is not null
if ($jwt) {
    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        
        // JWT is valid, now get user data from the database
        // The user ID is inside the 'data' part of the decoded token
        $user_id = $decoded->data->id;

        // Get database connection
        $database = new Database();
        $db = $database->getConnection();

        // Instantiate a user object
        $user = new User($db);

        // --- FETCH USER DATA ---
        // We can create a simple readOne method in the User class
        // For now, we'll do a direct query for simplicity
        $query = "SELECT user_id, username, email FROM users WHERE user_id = :user_id LIMIT 0,1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Set response code - 200 OK
            http_response_code(200);
            echo json_encode($row);
        } else {
            // Set response code - 404 Not Found
            http_response_code(404);
            echo json_encode(array("message" => "User not found."));
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