<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', __DIR__);

require_once ROOT_PATH . '/vendor/autoload.php';

// Load config
$config = require ROOT_PATH . '/config/config.php';

// Bootstrap app
$app = new App\Core\App($config);
(new App\Services\AuthService())->checkRememberMe();
$app->run();

