<?php
declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?? '', true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload.']);
    exit;
}

$targetPath = __DIR__ . '/data/content.json';
$backupPath = $targetPath . '.bak';

if (!is_dir(dirname($targetPath))) {
    http_response_code(500);
    echo json_encode(['error' => 'Content directory is missing.']);
    exit;
}

$json = json_encode(
    $data,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

if ($json === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to encode JSON.']);
    exit;
}

if (file_exists($targetPath)) {
    @copy($targetPath, $backupPath);
}

$result = file_put_contents($targetPath, $json . PHP_EOL, LOCK_EX);
if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to write content file.']);
    exit;
}

echo json_encode(['success' => true]);
