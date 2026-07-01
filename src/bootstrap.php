<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

// use vendor generated autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->loadEnv(__DIR__ . '/../.env', null, 'dev', ['test_install', 'test']);

return $loader;
