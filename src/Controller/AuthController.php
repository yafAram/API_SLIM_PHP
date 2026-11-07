<?php

declare(strict_types=1);

namespace App\Controller;

use App\Auth\TokenService;
use App\Http\Json;
use App\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AuthController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly TokenService $tokens,
    ) {
    }

    /**
     * POST /api/login — recibe username y password y devuelve un JWT.
     */
    public function login(Request $request, Response $response): Response
    {
        $params = (array) $request->getParsedBody();
        $username = trim((string) ($params['username'] ?? ''));
        $password = (string) ($params['password'] ?? '');

        if ($username === '' || $password === '') {
            return Json::respond($response, ['error' => 'Username and password required'], 400);
        }

        $user = $this->users->findByUsername($username);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return Json::respond($response, ['error' => 'Invalid credentials'], 401);
        }

        return Json::respond($response, [
            'token' => $this->tokens->issue($user['id'], $user['username']),
        ]);
    }

    /**
     * GET /api/secure — ruta protegida de ejemplo; devuelve el usuario del token.
     */
    public function secure(Request $request, Response $response): Response
    {
        $token = $request->getAttribute('token');

        return Json::respond($response, [
            'message' => 'This is a secure endpoint',
            'user' => $token->data->username ?? null,
        ]);
    }
}
