<?php

// Load initialization
require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../app/Helpers/MessageHelper.php';

// Load batch size from environment or default to 10
$batchSize = getenv('MESSAGE_BATCH_SIZE') ?: 10;

// Initialize MessageHelper
$messageHelper = new MessageHelper($pdo);

try {
	// Process messages
	echo "Starting message processing...\n";
	$messageHelper->processMessages((int)$batchSize);
	echo "Message processing complete.\n";
} catch (Exception $e) {
	// Log any unexpected errors
	echo "Error: " . $e->getMessage() . "\n";
	exit(1);
}
