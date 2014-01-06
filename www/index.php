<?php
define('WWW_DIR', dirname(__FILE__)); // path to the web root
define('IMG_DIR', WWW_DIR.'/images'); // path to images

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

// Let bootstrap create Dependency Injection container.
$container = require __DIR__ . '/../app/bootstrap.php';

// Run application.
$container->application->run();
