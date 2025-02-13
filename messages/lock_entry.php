<?php
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
    echo json_encode(["error" => "Invalid JSON input."]);
    exit;
}
if (isset($data['ids']) && is_array($data['ids'])) {
    $idsToLock = $data['ids'];
} elseif (isset($data['id'])) {
    $idsToLock = [$data['id']];
} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input. Expected 'id' or 'ids'."]);
    exit;
}
$unlockedFile = __DIR__ . '/messages.txt';
$lockedFile = __DIR__ . '/locked_messages.txt';
if (!file_exists($unlockedFile)) {
    echo json_encode(["success" => true, "message" => "No unlocked messages found."]);
    exit;
}
if (!is_writable($unlockedFile)) {
    http_response_code(500);
    echo json_encode(["error" => "Unlocked messages file is not writable."]);
    exit;
}
if (!file_exists($lockedFile)) {
    if (file_put_contents($lockedFile, "") === false) {
        http_response_code(500);
        echo json_encode(["error" => "Unable to create locked messages file."]);
        exit;
    }
}
if (!is_writable($lockedFile)) {
    http_response_code(500);
    echo json_encode(["error" => "Locked messages file is not writable."]);
    exit;
}
$lines = file($unlockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$newUnlockedLines = [];
$lockedEntries = [];
foreach ($lines as $line) {
    $entry = json_decode($line, true);
    if ($entry && isset($entry['id']) && in_array($entry['id'], $idsToLock)) {
        $lockedEntries[] = $line;
    } else {
        $newUnlockedLines[] = $line;
    }
}
$unlockedData = implode("\n", $newUnlockedLines);
if (!empty($unlockedData)) {
    $unlockedData .= "\n";
}
$result1 = file_put_contents($unlockedFile, $unlockedData, LOCK_EX);
if ($result1 === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update unlocked messages."]);
    exit;
}
if (!empty($lockedEntries)) {
    $textToAppend = implode("\n", $lockedEntries) . "\n";
    $result2 = file_put_contents($lockedFile, $textToAppend, FILE_APPEND | LOCK_EX);
    if ($result2 === false) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to append locked entries."]);
        exit;
    }
}
echo json_encode(["success" => true]);
?>
