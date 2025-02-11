<?php
// export.php

ini_set('display_errors', 0);
error_reporting(0);

$messagesFile = __DIR__ . '/messages.txt';
$messages = [];

if (file_exists($messagesFile)) {
    $lines = file($messagesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if ($entry) {
            $messages[] = $entry;
        }
    }
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="messages_export_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Time', 'Name', 'Email', 'Telephone', 'Message']);

foreach ($messages as $msg) {
    fputcsv($output, [
        $msg['time'] ?? '',
        $msg['name'] ?? '',
        $msg['email'] ?? '',
        $msg['tel'] ?? '',
        $msg['message'] ?? ''
    ]);
}

fclose($output);
exit;
?>
