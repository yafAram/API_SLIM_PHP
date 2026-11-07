<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\TokenService;
use App\Http\Json;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;
use UnexpectedValueException;

/**
 * Exige un encabezado "Authorization: Bearer <token>" válido.
 * Si lo es, deja el token decodificado en el atributo "token" de la petición.
 */
final class JwtMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly TokenService $tokens)
    {
    }

    public function process(Request $request, Handler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        if ($header === '') {
            return Json::respond(new SlimResponse(), ['error' => 'Token not provided'], 401);
        }

        if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            return Json::respond(new SlimResponse(), ['error' => 'Invalid token format'], 401);
        }

        try {
            $decoded = $this->tokens->verify($matches[1]);
        } catch (UnexpectedValueException) {
            return Json::respond(new SlimResponse(), ['error' => 'Invalid or expired token'], 401);
        }

        return $handler->handle($request->withAttribute('token', $decoded));
    }
}
