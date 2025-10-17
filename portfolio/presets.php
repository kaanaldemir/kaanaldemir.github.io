<?php declare(strict_types=1);

ini_set('display_errors', '1');

error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload.']);
    exit;
}

$action = $payload['action'] ?? '';
$presetsPath = __DIR__ . '/data/presets.json';
$presets = [];
if (is_readable($presetsPath)) {
    $json = file_get_contents($presetsPath);
    if ($json !== false) {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $presets = $decoded;
        }
    }
}

function persist_presets(string $path, array $presets): void
{
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        throw new RuntimeException('Preset directory is not writable.');
    }
    $json = json_encode($presets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Failed to encode presets: ' . json_last_error_msg());
    }
    if (file_put_contents($path, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('Unable to write presets file.');
    }
}

try {
    switch ($action) {
        case 'save':
            $name = trim((string)($payload['name'] ?? ''));
            $content = $payload['content'] ?? null;
            if ($name === '' || !is_array($content)) {
                http_response_code(400);
                echo json_encode(['error' => 'Preset name and content are required.']);
                exit;
            }
            $nameLength = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
            if ($nameLength > 80) {
                http_response_code(400);
                echo json_encode(['error' => 'Preset name is too long.']);
                exit;
            }
            $presets[$name] = $content;
            persist_presets($presetsPath, $presets);
            echo json_encode(['success' => true, 'presets' => $presets]);
            break;

        case 'load':
            $name = trim((string)($payload['name'] ?? ''));
            if ($name === '' || !array_key_exists($name, $presets)) {
                http_response_code(404);
                echo json_encode(['error' => 'Preset not found.']);
                exit;
            }
            echo json_encode(['success' => true, 'content' => $presets[$name]]);
            break;

        case 'delete':
            $name = trim((string)($payload['name'] ?? ''));
            if ($name === '' || !array_key_exists($name, $presets)) {
                http_response_code(404);
                echo json_encode(['error' => 'Preset not found.']);
                exit;
            }
            unset($presets[$name]);
            persist_presets($presetsPath, $presets);
            echo json_encode(['success' => true, 'presets' => $presets]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action.']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Presets error: ' . $e->getMessage()]);
}



