<?php

require __DIR__ . '/../vendor/autoload.php';

define('BASE_DIR', __DIR__ . '/..');
define('WEB_DIR', __DIR__);

use BootFrame\App;

$app = new App;
$app->run();
