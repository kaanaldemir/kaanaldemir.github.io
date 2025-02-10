<?php
// index.php

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Received Messages</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      body {
        font-family: Arial, sans-serif;
        background: #f0f4f8;
        color: #333;
        padding: 20px;
      }
      .container {
        max-width: 900px;
        margin: 0 auto;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
      }
      th, td {
        padding: 8px 12px;
        border: 1px solid #ccc;
      }
      th {
        background: #4A90E2;
        color: white;
      }
      tr:nth-child(even) {
        background: #eee;
      }
    </style>
</head>
<body>
<div class="container">
    <h1>Received Messages</h1>
    <?php if (empty($messages)): ?>
        <p>No messages received yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><?= htmlspecialchars($msg['time']) ?></td>
                    <td><?= htmlspecialchars($msg['name']) ?></td>
                    <td><?= htmlspecialchars($msg['email']) ?></td>
                    <td><?= htmlspecialchars($msg['tel']) ?></td>
                    <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
