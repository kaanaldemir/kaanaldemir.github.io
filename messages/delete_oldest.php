<?php
// delete_oldest.php

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

if (!$data || !isset($data['limit']) || !is_numeric($data['limit'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input. Expected numeric 'limit'."]);
    exit;
}

$limit = (int)$data['limit'];
if ($limit <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Limit must be greater than zero."]);
    exit;
}

$messagesFile = __DIR__ . '/messages.txt';

if (!file_exists($messagesFile)) {
    echo json_encode(["success" => true, "message" => "No messages found."]);
    exit;
}

// Read all lines, skipping empty ones.
$lines = file($messagesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$total = count($lines);

if ($total === 0) {
    echo json_encode(["success" => true, "message" => "No messages found."]);
    exit;
}

// Remove the first $limit lines (oldest entries).
$remainingLines = array_slice($lines, $limit);

$result = file_put_contents($messagesFile, implode("\n", $remainingLines) . (count($remainingLines) > 0 ? "\n" : ""), LOCK_EX);
if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to delete oldest entries."]);
    exit;
}

echo json_encode(["success" => true]);
?>
