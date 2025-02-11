<?php
// delete_all.php

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$messagesFile = __DIR__ . '/messages.txt';

if (!file_exists($messagesFile)) {
    echo json_encode(["success" => true, "message" => "No messages to clear."]);
    exit;
}

$result = file_put_contents($messagesFile, '');
if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to clear messages."]);
    exit;
}

echo json_encode(["success" => true]);
?>
