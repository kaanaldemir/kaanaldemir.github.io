<?php
// import_csv.php

ini_set('display_errors', 0);
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: index.php?import_error=' . urlencode('File upload error.'));
    exit;
}

$fileTmpPath = $_FILES['csv_file']['tmp_name'];
$handle = fopen($fileTmpPath, 'r');
if ($handle === false) {
    header('Location: index.php?import_error=' . urlencode('Could not open uploaded file.'));
    exit;
}

// Read the first row; if it matches expected headers, skip it.
$firstRow = fgetcsv($handle);
$expectedHeaders = ['Time', 'Name', 'Email', 'Telephone', 'Message'];
$hasHeader = false;
if ($firstRow) {
    $trimmedHeaders = array_map('trim', $firstRow);
    if (array_map('strtolower', $trimmedHeaders) === array_map('strtolower', $expectedHeaders)) {
        $hasHeader = true;
    } else {
        // No header detected, rewind so the first row is processed as data.
        rewind($handle);
    }
}

$messagesFile = __DIR__ . '/messages.txt';
$linesToAppend = [];
while (($data = fgetcsv($handle)) !== false) {
    // Expecting five columns: Time, Name, Email, Telephone, Message
    if (count($data) < 5) {
        continue;
    }
    list($time, $name, $email, $tel, $message) = $data;
    // Use current time if the CSV time field is empty
    $time = trim($time) !== '' ? trim($time) : date('Y-m-d H:i:s');
    $entry = [
        'id'      => uniqid(),
        'time'    => $time,
        'name'    => trim($name),
        'email'   => trim($email),
        'tel'     => trim($tel),
        'message' => trim($message),
    ];
    $linesToAppend[] = json_encode($entry);
}
fclose($handle);

if (!empty($linesToAppend)) {
    $linesText = implode("\n", $linesToAppend) . "\n";
    $result = file_put_contents($messagesFile, $linesText, FILE_APPEND | LOCK_EX);
    if ($result === false) {
        header('Location: index.php?import_error=' . urlencode('Failed to write messages.'));
        exit;
    }
}

header('Location: index.php?import_success=' . urlencode('CSV imported successfully.'));
exit;
?>
