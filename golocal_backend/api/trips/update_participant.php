<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Get data from the request
$data = json_decode(file_get_contents("php://input"));
$jwt = isset($data->jwt) ? $data->jwt : "";
$participant_id = isset($data->participant_id) ? $data->participant_id : "";

$new_name = isset($data->name) ? $data->name : null;
$new_email = isset($data->email) ? $data->email : null;
$new_status = isset($data->status) ? $data->status : null;
$new_is_co_admin = isset($data->is_co_admin) ? $data->is_co_admin : null;


if (!$jwt || !$participant_id) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
    return;
}

if ($new_name === null && $new_email === null && $new_status === null && $new_is_co_admin === null) {
    http_response_code(400);
    echo json_encode(array("message" => "No fields to update."));
    return;
}

try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $admin_id = $decoded->data->id;

    $database = new Database();
    $db = $database->getConnection();

    // First, get the trip_id from the participant_id to verify admin status
    $query = "SELECT trip_id FROM trip_participants WHERE participant_id = :participant_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':participant_id', $participant_id);
    $stmt->execute();
    $participant_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $trip_id = $participant_row['trip_id'];

    // Verify the user is an admin or co-admin of this trip, handling NULL co_admin_id
    $query = "SELECT trip_id FROM trips WHERE trip_id = :trip_id AND (admin_id = :admin_id OR (co_admin_id IS NOT NULL AND co_admin_id = :admin_id))";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':trip_id', $trip_id);
    $stmt->bindParam(':admin_id', $admin_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. You are not an admin for this trip."));
        return;
    }

    // Build dynamic update query
    $fields = array();
    if ($new_name !== null) $fields[] = "guest_name = :name";
    if ($new_email !== null) $fields[] = "guest_email = :email";
    if ($new_status !== null) $fields[] = "status = :status";
    if ($new_is_co_admin !== null) $fields[] = "is_co_admin = :is_co_admin";
    $setClause = implode(", ", $fields);
    $query = "UPDATE trip_participants SET $setClause WHERE participant_id = :participant_id";
    $stmt = $db->prepare($query);
    if ($new_name !== null) $stmt->bindParam(':name', $new_name);
    if ($new_email !== null) $stmt->bindParam(':email', $new_email);
    if ($new_status !== null) $stmt->bindParam(':status', $new_status);
    if ($new_is_co_admin !== null) $stmt->bindParam(':is_co_admin', $new_is_co_admin);
    $stmt->bindParam(':participant_id', $participant_id);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Participant updated successfully."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update participant."));
    }

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
}
?>