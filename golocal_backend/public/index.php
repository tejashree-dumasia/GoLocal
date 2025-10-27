<?php
// #1 --- SET GLOBAL HEADERS & HANDLE CORS PREFLIGHT ---
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// This handles the OPTIONS request sent by the browser for CORS.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// #2 --- INCLUDE CORE FILES & VENDOR AUTOLOADER ---
// This makes all your classes and vendor packages available globally.
require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/core.php'; // This defines $secret_key
include_once __DIR__ . '/../core/user.php';
include_once __DIR__ . '/../core/trip.php';
include_once __DIR__ . '/../core/user.php';
include_once __DIR__ . '/../core/trip.php';
include_once __DIR__ . '/../core/checklist.php';

// Add other core models here as you create them
// include_once __DIR__ . '/../core/photo.php';

// #3 --- PARSE THE REQUEST URL ---
// The URL is passed as a query parameter from .htaccess
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url_parts = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));

// #4 --- ROUTING LOGIC ---
// Determine which endpoint to load based on the URL parts.
// We expect the URL to start with 'api/'.
if (isset($url_parts[0]) && $url_parts[0] == 'api' && isset($url_parts[1])) {
    $resource = $url_parts[1]; // e.g., 'users' or 'trips'
    $action = isset($url_parts[2]) ? $url_parts[2] : 'index'; // e.g., 'read_single'

    // Construct the path to the endpoint file
    $api_file_path = __DIR__ . '/../api/' . $resource . '/' . $action . '.php';

    if (file_exists($api_file_path)) {
        // If the file exists, include it to handle the request.
        require_once $api_file_path;
    } else {
        // Endpoint file not found
        http_response_code(404);
        echo json_encode(['message' => 'API Endpoint Not Found']);
    }
} else {
    // Invalid API request format
    http_response_code(400);
    echo json_encode(['message' => 'Invalid API Request']);
}