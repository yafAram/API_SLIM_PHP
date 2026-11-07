<?php

declare(strict_types=1);

/**
 * Crea un usuario con la contraseña cifrada (password_hash).
 *
 * Uso: php bin/create-user.php <username> <password>
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Config;
use App\Database;
use App\DotEnv;

if ($argc !== 3) {
    fwrite(STDERR, "Uso: php bin/create-user.php <username> <password>\n");
    exit(1);
}

[, $username, $password] = $argv;

if (strlen($password) < 8) {
    fwrite(STDERR, "La contraseña debe tener al menos 8 caracteres.\n");
    exit(1);
}

$config = Config::fromEnv(DotEnv::load(__DIR__ . '/../.env'));
$pdo = Database::connect($config);

try {
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (:username, :hash)');
    $stmt->execute([
        'username' => $username,
        'hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, "No se pudo crear el usuario (¿ya existe?): {$e->getMessage()}\n");
    exit(1);
}

echo "Usuario '{$username}' creado.\n";
