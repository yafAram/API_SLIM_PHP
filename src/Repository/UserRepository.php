<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

/**
 * Acceso a la tabla `users` mediante consultas preparadas.
 */
final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{id: int|string, username: string, password_hash: string}|null
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, password_hash FROM users WHERE username = :username'
        );
        $stmt->execute(['username' => $username]);

        $user = $stmt->fetch();

        return $user === false ? null : $user;
    }
}
