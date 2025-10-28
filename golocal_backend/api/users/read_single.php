<?php
// This file is called by `public/index.php`, which handles core includes.
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get request headers
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
        // $secret_key is loaded from config/core.php in index.php
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        
        // JWT is valid, get the user ID
        $user_id = $decoded->data->id;

        // Get database connection
        $database = new Database();
        $db = $database->getConnection();

        // --- FETCH USER DATA ---
        // Updated query to include profile_picture_url
        $query = "SELECT user_id, username, email, profile_picture_url 
                  FROM users 
                  WHERE user_id = :user_id LIMIT 0,1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // --- Convert relative path to full URL ---
            if ($row['profile_picture_url']) {
                // Update this URL if your virtual host is different
                $row['profile_picture_url'] = 'http://127.0.0.1:8000/' . $row['profile_picture_url'];
            }

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