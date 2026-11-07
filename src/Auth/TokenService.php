<?php

declare(strict_types=1);

namespace App\Auth;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;
use UnexpectedValueException;

/**
 * Emite y valida tokens JWT firmados con HS256.
 */
final class TokenService
{
    public function __construct(
        private readonly string $secret,
        private readonly string $issuer,
        private readonly int $ttl,
    ) {
    }

    /**
     * @param int|string $userId
     */
    public function issue(int|string $userId, string $username): string
    {
        $now = new DateTimeImmutable();

        return JWT::encode([
            'iss' => $this->issuer,
            'iat' => $now->getTimestamp(),
            'nbf' => $now->getTimestamp(),
            'exp' => $now->getTimestamp() + $this->ttl,
            'data' => ['userId' => $userId, 'username' => $username],
        ], $this->secret, 'HS256');
    }

    /**
     * @throws UnexpectedValueException si el token es inválido, expiró o tiene otro emisor
     */
    public function verify(string $token): stdClass
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Throwable $e) {
            throw new UnexpectedValueException('Invalid or expired token', 0, $e);
        }

        if (($decoded->iss ?? null) !== $this->issuer) {
            throw new UnexpectedValueException('Invalid token issuer');
        }

        return $decoded;
    }
}
