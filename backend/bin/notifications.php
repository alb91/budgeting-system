<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Services\NotificationService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

NotificationService::processNotifications(); 

echo "Done.\n";