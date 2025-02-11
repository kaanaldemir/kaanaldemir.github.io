<?php
// save.php

// Disable error reporting
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON input"]);
    exit;
}

$name    = trim($data['name'] ?? '');
$email   = trim($data['email'] ?? '');
$tel     = trim($data['tel'] ?? '');
$message = trim($data['message'] ?? '');

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

$entry = [
    'id'      => uniqid(),
    'time'    => date('Y-m-d H:i:s', time() + 3 * 3600),
    'name'    => $name,
    'email'   => $email,
    'tel'     => $tel,
    'message' => $message,
];

$entry_line = json_encode($entry) . "\n";

$messagesFile = __DIR__ . '/messages.txt';

// If file exists and is not empty, ensure it ends with a newline.
if (file_exists($messagesFile) && filesize($messagesFile) > 0) {
    $contents = file_get_contents($messagesFile);
    if (substr($contents, -1) !== "\n") {
        $entry_line = "\n" . $entry_line;
    }
}

$result = file_put_contents($messagesFile, $entry_line, FILE_APPEND | LOCK_EX);
if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save message."]);
    exit;
}

echo json_encode(["success" => true]);
?>
