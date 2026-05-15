<?php

declare(strict_types=1);

@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/AuthGuard.php';
require_once __DIR__ . '/GymRepository.php';
