<?php
// export.php

ini_set('display_errors', 0);
error_reporting(0);

$unlockedFile = __DIR__ . '/messages.txt';
$lockedFile   = __DIR__ . '/locked_messages.txt';

$allMessages = [];

// Load unlocked messages and annotate as unlocked.
if (file_exists($unlockedFile)) {
    $lines = file($unlockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $entry['locked'] = "No";
            $allMessages[] = $entry;
        }
    }
}

// Load locked messages and annotate as locked.
if (file_exists($lockedFile)) {
    $lines = file($lockedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $entry['locked'] = "Yes";
            $allMessages[] = $entry;
        }
    }
}

// Optionally sort messages by time.
usort($allMessages, function($a, $b) {
    return strtotime($a['time']) - strtotime($b['time']);
});

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="messages_export_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');
// Write header row including "Locked"
fputcsv($output, ['Time', 'Name', 'Email', 'Telephone', 'Message', 'Locked']);

foreach ($allMessages as $msg) {
    fputcsv($output, [
        $msg['time'] ?? '',
        $msg['name'] ?? '',
        $msg['email'] ?? '',
        $msg['tel'] ?? '',
        $msg['message'] ?? '',
        $msg['locked'] ?? 'No'
    ]);
}

fclose($output);
exit;
?>
