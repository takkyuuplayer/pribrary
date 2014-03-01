<?php
define('PROJECT_DIR', __DIR__ . '/../..');
define('TIMEZONE', 'Asia/Tokyo');

if(file_exists(__DIR__ . '/amazon.php')) {
    require_once __DIR__ . '/amazon.php';
}
