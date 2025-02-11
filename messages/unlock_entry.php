<?php
// unlock_entry.php

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$input = file_get_contents('php://input');
$data  = json_decode($input, true);
if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input. Expected 'id'."]);
    exit;
}

$id = $data['id'];
$lockedFile   = __DIR__ . '/locked_messages.txt';
$unlockedFile = __DIR__ . '/messages.txt';

if (!file_exists($lockedFile)) {
    echo json_encode(["error" => "No locked messages found."]);
    exit;
}

$lines = file($lockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$found = false;
$newLockedLines = [];
$entryToUnlock = null;
foreach ($lines as $line) {
    $entry = json_decode($line, true);
    if ($entry && isset($entry['id']) && $entry['id'] == $id) {
        $found = true;
        $entryToUnlock = $entry;
        continue; // remove from locked messages
    }
    $newLockedLines[] = $line;
}

if (!$found) {
    echo json_encode(["error" => "Locked entry not found."]);
    exit;
}

// Save updated locked messages
$result = file_put_contents($lockedFile, implode("\n", $newLockedLines) . (count($newLockedLines) > 0 ? "\n" : ""), LOCK_EX);
if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update locked messages."]);
    exit;
}

// Append the unlocked entry back to messages.txt
$unlockedEntryLine = json_encode($entryToUnlock) . "\n";
$result = file_put_contents($unlockedFile, $unlockedEntryLine, FILE_APPEND | LOCK_EX);
if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to unlock entry."]);
    exit;
}

echo json_encode(["success" => true]);
?>
