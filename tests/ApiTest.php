<?php

declare(strict_types=1);

use function Tests\app;
use function Tests\assertSame;
use function Tests\assertTrue;
use function Tests\body;
use function Tests\call;

return [
    'login con credenciales correctas devuelve un token' => function () {
        $response = call(app(), 'POST', '/api/login', ['username' => 'ana', 'password' => 'secreto123']);

        assertSame(200, $response->getStatusCode());
        assertTrue(isset(body($response)['token']), 'falta el token');
    },

    'login sin datos responde 400' => function () {
        $response = call(app(), 'POST', '/api/login', ['username' => '', 'password' => '']);

        assertSame(400, $response->getStatusCode());
    },

    'login con contraseña incorrecta responde 401' => function () {
        $response = call(app(), 'POST', '/api/login', ['username' => 'ana', 'password' => 'mala']);

        assertSame(401, $response->getStatusCode());
        assertSame('Invalid credentials', body($response)['error']);
    },

    'login con usuario inexistente responde 401' => function () {
        $response = call(app(), 'POST', '/api/login', ['username' => 'nadie', 'password' => 'secreto123']);

        assertSame(401, $response->getStatusCode());
    },

    'ruta protegida sin token responde 401' => function () {
        $response = call(app(), 'GET', '/api/secure');

        assertSame(401, $response->getStatusCode());
        assertSame('Token not provided', body($response)['error']);
    },

    'ruta protegida con formato de token inválido responde 401' => function () {
        $response = call(app(), 'GET', '/api/secure', null, ['Authorization' => 'Token abc']);

        assertSame(401, $response->getStatusCode());
        assertSame('Invalid token format', body($response)['error']);
    },

    'ruta protegida con un token válido responde 200' => function () {
        $app = app();
        $token = body(call($app, 'POST', '/api/login', ['username' => 'ana', 'password' => 'secreto123']))['token'];

        $response = call($app, 'GET', '/api/secure', null, ['Authorization' => "Bearer {$token}"]);

        assertSame(200, $response->getStatusCode());
        assertSame('ana', body($response)['user']);
    },
];
