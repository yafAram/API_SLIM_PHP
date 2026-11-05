<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Crea la conexión PDO a partir de la configuración.
 */
final class Database
{
    public static function connect(Config $config): PDO
    {
        return new PDO($config->dbDsn, $config->dbUser, $config->dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
