<?php

function loadEnv()
{
    $envFile = __DIR__ . '/../.env';

    if (!file_exists($envFile)) {
        die("Environment file (.env) not found.");
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;

        // Parse key-value pairs
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }

    // Store environment variables globally for access
    foreach ($env as $key => $value) {
        putenv("$key=$value"); // Available via getenv()
        $_ENV[$key] = $value;  // Available via $_ENV
    }
}
