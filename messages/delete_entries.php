<?php
// delete_entries.php

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

if (!$data || !isset($data['ids']) || !is_array($data['ids'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input. Expected 'ids' array."]);
    exit;
}

$idsToDelete = $data['ids'];
$messagesFile = __DIR__ . '/messages.txt';

if (!file_exists($messagesFile)) {
    echo json_encode(["success" => true, "message" => "No messages found."]);
    exit;
}

$lines = file($messagesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$remainingLines = [];
foreach ($lines as $line) {
    $entry = json_decode($line, true);
    if ($entry && isset($entry['id'])) {
        if (in_array($entry['id'], $idsToDelete)) {
            continue;
        }
    }
    $remainingLines[] = $line;
}

$result = file_put_contents($messagesFile, implode("\n", $remainingLines) . "\n", LOCK_EX);
if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to delete entries."]);
    exit;
}

echo json_encode(["success" => true]);
?>
