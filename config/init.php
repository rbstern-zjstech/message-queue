<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';
loadEnv();
require_once __DIR__ . '/database.php';

$isCron = (PHP_SAPI === 'cli');

error_reporting(E_ALL);
ini_set('display_errors', true);

date_default_timezone_set(getenv('SITE_TIME_ZONE'));
