<?php

declare(strict_types=1);

namespace Tests;

use App\AppFactory;
use App\Config;
use PDO;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

const SECRET = 'una-clave-de-prueba-de-al-menos-32-caracteres';

function assertSame(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            ($message !== '' ? $message . ': ' : '')
            . 'esperado ' . var_export($expected, true) . ', obtenido ' . var_export($actual, true)
        );
    }
}

function assertTrue(bool $condition, string $message = 'la condición es falsa'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function config(array $overrides = []): Config
{
    return Config::fromEnv($overrides + ['JWT_SECRET' => SECRET, 'JWT_ISSUER' => 'tests', 'JWT_TTL' => '3600']);
}

/**
 * Aplicación con una base SQLite en memoria y un usuario "ana" / "secreto123".
 */
function app(): App
{
    $pdo = new PDO('sqlite::memory:', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE NOT NULL, password_hash TEXT NOT NULL)');
    $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)')
        ->execute(['ana', password_hash('secreto123', PASSWORD_DEFAULT)]);

    return AppFactory::create(config(), $pdo);
}

/**
 * @param array<string, mixed>|null $json
 * @param array<string, string> $headers
 */
function call(App $app, string $method, string $path, ?array $json = null, array $headers = []): ResponseInterface
{
    $request = (new ServerRequestFactory())->createServerRequest($method, $path);

    foreach ($headers as $name => $value) {
        $request = $request->withHeader($name, $value);
    }

    if ($json !== null) {
        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody((new StreamFactory())->createStream(json_encode($json)));
    }

    return $app->handle($request);
}

/**
 * @return array<string, mixed>
 */
function body(ResponseInterface $response): array
{
    return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
}
