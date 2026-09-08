<?php
// TravelVista - application configuration. Edit the database block to match
// your MySQL / MariaDB server.

// Database
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'travelvista');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application
define('APP_NAME', 'TravelVista');
define('APP_TAGLINE', 'A field guide to the world, filed by the people who went.');

// Project root: forward slashes, no trailing slash.
define('APP_ROOT', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)));

// Uploads
define('UPLOAD_DIR', APP_ROOT . '/public/uploads');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);          // 2 MB per image
define('ALLOWED_IMAGE_MIME', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Sessions and cookies
define('SESSION_NAME', 'travelvista_session');
define('REMEMBER_COOKIE', 'travelvista_remember');
define('REMEMBER_DAYS', 30);

// Domain vocabulary
define('GENRES', ['beach', 'mountain', 'city', 'historical', 'island', 'desert', 'wildlife']);
define('TRAVEL_MEDIUMS', ['flight', 'train', 'bus', 'ferry', 'car', 'on foot']);

// Base trip cost in USD per cost level (PRD mapping).
define('COST_LEVELS', ['low', 'medium', 'high']);
define('COST_BASE', ['low' => 500, 'medium' => 1500, 'high' => 3000]);

// URL prefix the project is served from. Must match the project folder name;
// change this if you rename the folder.
define('BASE_URL', '/TravelVista');
