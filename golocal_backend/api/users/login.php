<?php
use Firebase\JWT\JWT;

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate a user object
$user = new User($db);

// Get the posted data
$data = json_decode(file_get_contents("php://input"));

// --- VALIDATION ---
if (!empty($data->email) && !empty($data->password)) {
    // Set user property values
    $user->email = $data->email;
    $user->password = $data->password;

    // Attempt to log the user in
    if ($user->login()) {
        // User logged in successfully, create the token
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array( // Payload
                "id" => $user->user_id,
                "username" => $user->username,
                "email" => $user->email
            )
        );

        // Set response code - 200 OK
        http_response_code(200);

        // Generate the JWT
        $jwt = JWT::encode($token, $secret_key, 'HS256');
        echo json_encode(
            array(
                "message" => "Successful login.",
                "jwt" => $jwt
            )
        );
    } else {
        // Set response code - 401 Unauthorized
        http_response_code(401);
        echo json_encode(array("message" => "Login failed. Invalid credentials."));
    }
} else {
    // Set response code - 400 Bad Request
    http_response_code(400);
    echo json_encode(array("message" => "Unable to login. Data is incomplete."));
}