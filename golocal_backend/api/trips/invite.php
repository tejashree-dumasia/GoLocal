<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get data from the request
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$trip_id = isset($data->trip_id) ? $data->trip_id : "";
$invitee_email = isset($data->email) ? trim($data->email) : "";
$invitee_name = isset($data->name) ? trim($data->name) : "";

// Validate JWT and that we have at least an email or a name
if (!$jwt || !$trip_id || (empty($invitee_email) && empty($invitee_name))) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
    return;
}

try {
    include_once __DIR__ . '/../../config/core.php';
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $inviter_id = $decoded->data->id;
    
    $database = new Database();
    $db = $database->getConnection();

    // 1. Verify the person inviting is the trip admin (security check)
    $query = "SELECT trip_id FROM trips 
          WHERE trip_id = :trip_id 
          AND (admin_id = :admin_id OR co_admin_id = :admin_id)";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':admin_id', $inviter_id); // The inviter's ID
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. You are not the admin or co-admin of this trip."));
        return;
    }
    
    // --- Logic to Add Participant ---
    $user_id_to_add = null;
    
    // Scenario 1 & 2: An email is provided
    if (!empty($invitee_email)) {
        // Check if this email belongs to a registered user
        $query = "SELECT user_id, username FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $invitee_email);
        $stmt->execute();
        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_row) {
            // User exists, we will add them by their user_id
            $user_id_to_add = $user_row['user_id'];
            $invitee_name = $user_row['username']; // Use their registered username
        }
        
        // If user does not exist, they will be added as a guest with their email.
        // The $user_id_to_add will remain null.
    }

    // Insert into participants table
    $query = "INSERT INTO trip_participants (trip_id, user_id, guest_name, guest_email, status) 
              VALUES (:trip_id, :user_id, :guest_name, :guest_email, 'invited')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':user_id', $user_id_to_add);
    $stmt->bindParam(':guest_name', $invitee_name);
    
    // Only bind email if it was provided and the user wasn't found
    $guest_email_to_add = $user_id_to_add ? null : $invitee_email;
    $stmt->bindParam(':guest_email', $guest_email_to_add);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Participant successfully added."));
        // You could add logic here to send an email if $guest_email_to_add is not null
    } else {
        http_response_code(409);
        echo json_encode(array("message" => "Unable to add participant. They may already be in the trip."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>