<?php
// relay.php
// This file relays incoming requests to the protected save.php file located in /messages/

// (Optional) Perform any extra security or validation here if needed.

// Include the protected save.php file.
// Using require_once ensures that the file is loaded and that execution stops if it isn’t.
require_once __DIR__ . '/messages/save.php';
