<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\AppFactory;
use App\Config;
use App\Database;
use App\DotEnv;

// La configuración (base de datos y clave JWT) viene del entorno o del archivo .env
$config = Config::fromEnv(DotEnv::load(__DIR__ . '/../.env'));

AppFactory::create($config, Database::connect($config))->run();
