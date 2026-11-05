<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use App\Config;
use App\Database;
use App\DotEnv;
use App\Repository\UserRepository;

// La configuración (base de datos y clave JWT) viene del entorno o del archivo .env
$config = Config::fromEnv(DotEnv::load(__DIR__ . '/../.env'));

$app = AppFactory::create();

// Habilitar middleware de errores; los detalles solo se muestran con APP_DEBUG=true
$app->addErrorMiddleware($config->debug, true, true);

// Conexión a la base de datos (PDO) y repositorio de usuarios
$users = new UserRepository(Database::connect($config));

// Clave secreta para firmar el token JWT (se define en JWT_SECRET)
$secretKey = $config->jwtSecret;

// Ruta de Login: Recibe username y password, y si son válidos, retorna un JWT
$app->post('/api/login', function (Request $request, Response $response, $args) use ($users, $secretKey, $config) {
    $params   = (array)$request->getParsedBody();
    $username = trim($params['username'] ?? '');
    $password = $params['password'] ?? '';

    if (empty($username) || empty($password)) {
        $data = ['error' => 'Username and password required'];
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $user = $users->findByUsername($username);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $data = ['error' => 'Invalid credentials'];
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    // Generación del token JWT
    $issuedAt   = new DateTimeImmutable();
    $expire     = $issuedAt->modify('+' . $config->jwtTtl . ' seconds')->getTimestamp(); // Vigencia: JWT_TTL
    $serverName = $config->jwtIssuer;

    $tokenData = [
        'iat'  => $issuedAt->getTimestamp(),         // Tiempo de emisión
        'iss'  => $serverName,                         // Emisor del token
        'nbf'  => $issuedAt->getTimestamp(),           // No válido antes de
        'exp'  => $expire,                             // Expiración
        'data' => [
            'userId'   => $user['id'],
            'username' => $user['username'],
        ],
    ];

    $jwt = JWT::encode($tokenData, $secretKey, 'HS256');

    $payload = ['token' => $jwt];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json');
});

// Middleware para validar el token JWT en rutas protegidas
$jwtMiddleware = function (Request $request, $handler) use ($secretKey) {
    $authHeader = $request->getHeader('Authorization');
    if (!$authHeader) {
        $data = ['error' => 'Token not provided'];
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
    $parts = explode(" ", $authHeader[0]);
    if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
        $data = ['error' => 'Invalid token format'];
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $token = $parts[1];
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    } catch (\Exception $e) {
        $data = ['error' => 'Invalid token: ' . $e->getMessage()];
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    // Token válido, se puede agregar la información decodificada al request si se desea
    return $handler->handle($request);
};

// Ruta protegida de ejemplo: solo accesible con un token JWT válido
$app->get('/api/secure', function (Request $request, Response $response, $args) {
    $data = ['message' => 'This is a secure endpoint'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
})->add($jwtMiddleware);

$app->run();
