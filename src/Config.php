<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

/**
 * Configuración de la aplicación, obtenida de variables de entorno.
 */
final class Config
{
    private const MIN_SECRET_LENGTH = 32;

    public function __construct(
        public readonly string $dbDsn,
        public readonly string $dbUser,
        public readonly string $dbPassword,
        public readonly string $jwtSecret,
        public readonly string $jwtIssuer,
        public readonly int $jwtTtl,
        public readonly bool $debug,
    ) {
    }

    /**
     * @param array<string, string> $env
     */
    public static function fromEnv(array $env): self
    {
        $secret = $env['JWT_SECRET'] ?? '';
        if (strlen($secret) < self::MIN_SECRET_LENGTH) {
            throw new RuntimeException(
                'JWT_SECRET debe estar definido y tener al menos ' . self::MIN_SECRET_LENGTH . ' caracteres.'
            );
        }

        $dsn = $env['DB_DSN'] ?? sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'] ?? 'localhost',
            $env['DB_PORT'] ?? '3306',
            $env['DB_NAME'] ?? 'api_db',
        );

        return new self(
            dbDsn: $dsn,
            dbUser: $env['DB_USER'] ?? 'root',
            dbPassword: $env['DB_PASSWORD'] ?? '',
            jwtSecret: $secret,
            jwtIssuer: $env['JWT_ISSUER'] ?? 'api-slim-php',
            jwtTtl: (int) ($env['JWT_TTL'] ?? 3600),
            debug: filter_var($env['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
        );
    }
}
