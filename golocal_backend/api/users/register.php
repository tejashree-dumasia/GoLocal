<?php
// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate a new user object
$user = new User($db);

// Get the posted data from the request body
$data = json_decode(file_get_contents("php://input"));

// --- VALIDATION ---
// Check if data is not empty
if (
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    // Set user property values
    $user->username = $data->username;
    $user->email = $data->email;
    $user->password = $data->password;

    // Attempt to create the user
    if ($user->create()) {
        // Set response code - 201 Created
        http_response_code(201);
        echo json_encode(array("message" => "User was successfully registered."));
    } else {
        // Check if it failed because the email already exists
        if ($user->emailExists()) {
             // Set response code - 409 Conflict
            http_response_code(409);
            echo json_encode(array("message" => "Unable to register. Email already exists."));
        } else {
            // Set response code - 503 Service Unavailable (for other errors)
            http_response_code(503);
            echo json_encode(array("message" => "Unable to register user."));
        }
    }
} else {
    // Set response code - 400 Bad Request
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register user. Data is incomplete."));
}