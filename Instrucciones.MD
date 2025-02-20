# Instalación de Composer y Slim Framework

## 1. Descargar e Instalar Composer

1. Descarga el instalador de Composer desde la siguiente página:
   - [Composer Download](https://getcomposer.org/download/)
2. Ejecuta el archivo `composer-setup.exe` y sigue las instrucciones de instalación.

---

## 2. Instalación Manual de Composer

Si deseas instalar Composer manualmente, ejecuta los siguientes comandos en la terminal de tu proyecto:

```sh
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

---

## 3. Configurar Variables de Entorno

Si usas **XAMPP**, agrega la siguiente ruta a las variables de entorno:

```
C:\xampp\php\
```

Para verificar que PHP está correctamente configurado, ejecuta en la terminal:

```sh
php -v
```

Si muestra la versión de PHP, está correctamente configurado.

---

## 4. Instalación de Slim Framework

Ejecuta el siguiente comando en la terminal de tu proyecto:

```sh
composer require slim/slim
```

### 4.1 Instalación de Dependencias Adicionales

```sh
composer require slim/psr7
```

### 4.2 Instalación del Motor de Plantillas

```sh
composer require slim/twig-view
```

---

## 5. Creación de la Carpeta `public` y Archivo `index.php`

Crea una carpeta llamada `public` dentro de tu proyecto y dentro de ella un archivo `index.php` con el siguiente código:

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

// Crea la instancia de la aplicación
$app = AppFactory::create();

// Agrega middleware para mostrar errores (en desarrollo)
$app->addErrorMiddleware(true, true, true);

// Ruta GET: Devuelve una lista de usuarios en formato JSON
$app->get('/api/users', function (Request $request, Response $response, $args) {
    // Ejemplo de datos
    $users = [
        ['id' => 1, 'name' => 'Juan'],
        ['id' => 2, 'name' => 'María'],
    ];
    
    $payload = json_encode($users, JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Ruta POST: Crea un nuevo usuario (simulación)
$app->post('/api/users', function (Request $request, Response $response, $args) {
    // Obtiene los datos enviados
    $data = $request->getParsedBody();
    
    $result = [
        'status'  => 'success',
        'message' => 'Usuario creado',
        'data'    => $data
    ];
    
    $payload = json_encode($result, JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Ejecuta la aplicación
$app->run();
```

---

## 6. Iniciar el Servidor

Ejecuta el siguiente comando en la terminal:

```sh
php -S localhost:8080 -t public
```

Para comprobar que todo funciona correctamente, abre en tu navegador:

```
http://localhost:8080/api/users
```

### Respuesta esperada:

```json
[
    {"id": 1, "name": "Juan"},
    {"id": 2, "name": "María"}
]
```

---

## 🎉 ¡Listo! Ahora puedes comenzar a desarrollar con Slim Framework. 🚀

