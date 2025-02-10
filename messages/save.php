<?php
// Enable error reporting for debugging (remove these lines in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

// Read the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON input"]);
    exit;
}

// Extract and trim values
$name    = trim($data['name'] ?? '');
$email   = trim($data['email'] ?? '');
$tel     = trim($data['tel'] ?? '');
$message = trim($data['message'] ?? '');

// Basic validation: ensure message is at least 10 characters and one contact field is provided
if (strlen($message) < 10) {
    http_response_code(400);
    echo json_encode(["error" => "Message is too short."]);
    exit;
}
if ($name === '' && $email === '' && $tel === '') {
    http_response_code(400);
    echo json_encode(["error" => "At least one contact field is required."]);
    exit;
}

// Prepare the message entry with a timestamp
$entry = [
    'time'    => date('Y-m-d H:i:s'),
    'name'    => $name,
    'email'   => $email,
    'tel'     => $tel,
    'message' => $message,
];

// Convert entry to JSON and add a newline
$entry_line = json_encode($entry) . "\n";

// Define the file path where messages will be stored (messages.txt in the same directory)
$messagesFile = __DIR__ . '/messages.txt';

// Attempt to append the new message entry to the file
$result = file_put_contents($messagesFile, $entry_line, FILE_APPEND | LOCK_EX);
if ($result === false) {
    // Get the last error details
    $error = error_get_last();
    http_response_code(500);
    echo json_encode(["error" => "Failed to save message.", "details" => $error]);
    exit;
}

echo json_encode(["success" => true]);
?>
