<?php

declare(strict_types=1);

namespace App;

use App\Auth\TokenService;
use App\Controller\AuthController;
use App\Middleware\JwtMiddleware;
use App\Repository\UserRepository;
use PDO;
use Slim\App;
use Slim\Factory\AppFactory as SlimAppFactory;

/**
 * Construye la aplicación Slim con sus rutas; recibe la conexión ya creada
 * para poder usarla igual en producción y en pruebas.
 */
final class AppFactory
{
    public static function create(Config $config, PDO $pdo): App
    {
        $tokens = new TokenService($config->jwtSecret, $config->jwtIssuer, $config->jwtTtl);
        $auth = new AuthController(new UserRepository($pdo), $tokens);

        $app = SlimAppFactory::create();
        $app->addBodyParsingMiddleware();
        $app->addErrorMiddleware($config->debug, true, true);

        $app->post('/api/login', [$auth, 'login']);
        $app->get('/api/secure', [$auth, 'secure'])->add(new JwtMiddleware($tokens));

        return $app;
    }
}
