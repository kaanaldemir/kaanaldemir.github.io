<?php
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
$expectedHeaders5 = ['Time', 'Name', 'Email', 'Telephone', 'Message'];
$expectedHeaders6 = ['Time', 'Name', 'Email', 'Telephone', 'Message', 'Locked'];
$hasHeader = false;
$firstRow = fgetcsv($handle);
if ($firstRow) {
    $trimmedHeaders = array_map('trim', $firstRow);
    if (count($trimmedHeaders) === 6 && array_map('strtolower', $trimmedHeaders) === array_map('strtolower', $expectedHeaders6)) {
        $hasHeader = true;
    } elseif (count($trimmedHeaders) === 5 && array_map('strtolower', $trimmedHeaders) === array_map('strtolower', $expectedHeaders5)) {
        $hasHeader = true;
    } else {
        rewind($handle);
    }
}
$unlockedFile = __DIR__ . '/messages.txt';
$lockedFile = __DIR__ . '/locked_messages.txt';
$unlockedLinesToAppend = [];
$lockedLinesToAppend = [];
while (($data = fgetcsv($handle)) !== false) {
    if (count($data) < 5) {
        continue;
    }
    list($time, $name, $email, $tel, $message) = $data;
    $time = trim($time) !== '' ? trim($time) : date('Y-m-d H:i:s');
    $locked = false;
    if (count($data) >= 6) {
        $locked = (strtolower(trim($data[5])) === "yes");
    }
    $entry = [
        'id' => uniqid(),
        'time' => $time,
        'name' => trim($name),
        'email' => trim($email),
        'tel' => trim($tel),
        'message' => trim($message),
    ];
    $jsonLine = json_encode($entry);
    if ($locked) {
        $lockedLinesToAppend[] = $jsonLine;
    } else {
        $unlockedLinesToAppend[] = $jsonLine;
    }
}
fclose($handle);
$importError = '';
if (!empty($unlockedLinesToAppend)) {
    $linesText = implode("\n", $unlockedLinesToAppend) . "\n";
    $result = file_put_contents($unlockedFile, $linesText, FILE_APPEND | LOCK_EX);
    if ($result === false) {
        $importError = 'Failed to write unlocked messages.';
    }
}
if (!empty($lockedLinesToAppend)) {
    $linesText = implode("\n", $lockedLinesToAppend) . "\n";
    $result = file_put_contents($lockedFile, $linesText, FILE_APPEND | LOCK_EX);
    if ($result === false) {
        $importError = 'Failed to write locked messages.';
    }
}
if ($importError !== '') {
    header('Location: index.php?import_error=' . urlencode($importError));
    exit;
}
header('Location: index.php?import_success=' . urlencode('CSV imported successfully.'));
exit;
?>
