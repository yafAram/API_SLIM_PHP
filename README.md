# API Slim PHP

API REST con **PHP 8 y Slim 4** que autentica usuarios con **JWT** y consulta una base de datos con **PDO** (consultas preparadas). El código está separado en capas (configuración, acceso a datos, servicio de tokens, middleware y controlador) y cuenta con pruebas automáticas.

## Características

- Login con usuario y contraseña (`password_hash` / `password_verify`) que devuelve un JWT firmado con HS256.
- Ruta protegida mediante un middleware PSR-15 que valida firma, vigencia y emisor del token.
- Configuración por variables de entorno (`.env`); sin secretos en el código y con validación de la longitud de `JWT_SECRET`.
- Acceso a MySQL / MariaDB con PDO y consultas preparadas contra inyección SQL.
- El login acepta datos de formulario y JSON.
- Pruebas automáticas (login, rutas protegidas, tokens, configuración) que se ejecutan con SQLite en memoria.
- Dockerfile y `docker-compose` para levantar la API junto con MySQL.

## Requisitos

- PHP 8.1 o superior con la extensión `pdo_mysql`
- [Composer](https://getcomposer.org/)
- MySQL 8 / MariaDB (o Docker)

## Puesta en marcha

```bash
git clone https://github.com/yafAram/API_SLIM_PHP.git
cd API_SLIM_PHP
composer install
cp .env.example .env
```

Edita `.env` y define al menos `JWT_SECRET` (mínimo 32 caracteres):

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Crea la base de datos, un usuario y arranca el servidor:

```bash
mysql -u root -p < database/schema.sql
php bin/create-user.php ana secreto123
composer start        # http://localhost:8080
```

### Con Docker

```bash
cp .env.example .env   # define JWT_SECRET
docker compose up --build
docker compose exec api php bin/create-user.php ana secreto123
```

La API queda en `http://localhost:8080`. La base de datos solo es accesible desde la red de Compose.

## Endpoints

| Método | Ruta          | Autenticación | Descripción                              |
|--------|---------------|---------------|------------------------------------------|
| POST   | `/api/login`  | No            | Recibe `username` y `password`; devuelve un token |
| GET    | `/api/secure` | Bearer JWT    | Ruta protegida de ejemplo                |

```bash
# Login (JSON)
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"ana","password":"secreto123"}'
# {"token":"eyJ0eXAiOiJKV1Qi..."}

# Ruta protegida
curl http://localhost:8080/api/secure -H "Authorization: Bearer <token>"
# {"message":"This is a secure endpoint","user":"ana"}
```

| Código | Cuándo                                                          |
|--------|-----------------------------------------------------------------|
| 200    | Credenciales o token válidos                                    |
| 400    | Falta `username` o `password`                                   |
| 401    | Credenciales incorrectas, token ausente, mal formado, vencido o con otro emisor |

## Variables de entorno

| Variable      | Por defecto    | Descripción                                   |
|---------------|----------------|-----------------------------------------------|
| `JWT_SECRET`  | (obligatoria)  | Clave de firma, mínimo 32 caracteres          |
| `JWT_ISSUER`  | `api-slim-php` | Emisor (`iss`) del token                      |
| `JWT_TTL`     | `3600`         | Vigencia del token en segundos                |
| `DB_HOST`, `DB_PORT`, `DB_NAME` | `localhost`, `3306`, `api_db` | Conexión a la base de datos |
| `DB_USER`, `DB_PASSWORD` | `root`, (vacía) | Credenciales de la base de datos   |
| `DB_DSN`      | (se arma con las anteriores) | DSN completo de PDO, opcional   |
| `APP_DEBUG`   | `false`        | Muestra detalles de los errores               |

## Pruebas

```bash
composer test
```

Ejecutan 15 pruebas sin dependencias externas ni MySQL: arrancan la aplicación Slim con SQLite en memoria y verifican los códigos de respuesta, la validación de tokens y la configuración.

## Estructura

```
├── bin/create-user.php          # Crea usuarios desde la terminal
├── database/schema.sql          # Tabla users (MySQL / MariaDB)
├── docs/INSTALACION.md          # Guía de instalación de Composer y Slim
├── public/index.php             # Punto de entrada
├── src/
│   ├── AppFactory.php           # Rutas y middleware de Slim
│   ├── Auth/TokenService.php    # Emisión y validación de JWT
│   ├── Config.php, DotEnv.php   # Configuración desde el entorno
│   ├── Controller/              # AuthController (login y ruta protegida)
│   ├── Database.php             # Conexión PDO
│   ├── Http/Json.php            # Respuestas JSON
│   ├── Middleware/              # JwtMiddleware
│   └── Repository/              # UserRepository
└── tests/                       # Pruebas automáticas
```

## Tecnologías

PHP 8 · Slim 4 · slim/psr7 · firebase/php-jwt · PDO · MySQL · Docker · Composer

## Autor

**Yafté Aram Mercado Meza** · [GitHub](https://github.com/yafAram)
