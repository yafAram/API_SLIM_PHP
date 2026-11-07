<?php

declare(strict_types=1);

use App\Config;
use App\DotEnv;

use function Tests\assertSame;
use function Tests\assertTrue;

return [
    'rechaza un JWT_SECRET ausente' => function () {
        try {
            Config::fromEnv([]);
        } catch (RuntimeException) {
            return;
        }
        throw new RuntimeException('Debió lanzar RuntimeException');
    },

    'rechaza un JWT_SECRET demasiado corto' => function () {
        try {
            Config::fromEnv(['JWT_SECRET' => 'corta']);
        } catch (RuntimeException) {
            return;
        }
        throw new RuntimeException('Debió lanzar RuntimeException');
    },

    'usa valores por defecto razonables' => function () {
        $config = Config::fromEnv(['JWT_SECRET' => str_repeat('a', 32)]);

        assertSame('mysql:host=localhost;port=3306;dbname=api_db;charset=utf8mb4', $config->dbDsn);
        assertSame(3600, $config->jwtTtl);
        assertSame(false, $config->debug);
    },

    'DotEnv lee el archivo e ignora comentarios' => function () {
        $path = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($path, "# comentario\nDB_NAME=mi_base\nJWT_ISSUER=\"emisor\"\n\nvacia\n");

        $values = DotEnv::load($path);
        unlink($path);

        assertSame('mi_base', $values['DB_NAME']);
        assertSame('emisor', $values['JWT_ISSUER']);
        assertTrue(!isset($values['vacia']));
    },
];
