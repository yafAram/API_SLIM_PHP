<?php

declare(strict_types=1);

use App\Auth\TokenService;
use Firebase\JWT\JWT;

use function Tests\assertSame;

const TEST_SECRET = 'una-clave-de-prueba-de-al-menos-32-caracteres';

return [
    'emite un token que se puede verificar' => function () {
        $tokens = new TokenService(TEST_SECRET, 'tests', 60);

        $decoded = $tokens->verify($tokens->issue(7, 'ana'));

        assertSame('ana', $decoded->data->username);
        assertSame(7, $decoded->data->userId);
        assertSame('tests', $decoded->iss);
    },

    'rechaza un token firmado con otra clave' => function () {
        $forged = JWT::encode(['iss' => 'tests', 'exp' => time() + 60], str_repeat('x', 40), 'HS256');

        try {
            (new TokenService(TEST_SECRET, 'tests', 60))->verify($forged);
        } catch (UnexpectedValueException) {
            return;
        }
        throw new RuntimeException('Debió rechazar la firma inválida');
    },

    'rechaza un token vencido' => function () {
        $expired = JWT::encode(['iss' => 'tests', 'exp' => time() - 120], TEST_SECRET, 'HS256');

        try {
            (new TokenService(TEST_SECRET, 'tests', 60))->verify($expired);
        } catch (UnexpectedValueException) {
            return;
        }
        throw new RuntimeException('Debió rechazar el token vencido');
    },

    'rechaza un token de otro emisor' => function () {
        $other = JWT::encode(['iss' => 'otro', 'exp' => time() + 60], TEST_SECRET, 'HS256');

        try {
            (new TokenService(TEST_SECRET, 'tests', 60))->verify($other);
        } catch (UnexpectedValueException) {
            return;
        }
        throw new RuntimeException('Debió rechazar el emisor distinto');
    },
];
