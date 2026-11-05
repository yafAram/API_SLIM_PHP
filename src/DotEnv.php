<?php

declare(strict_types=1);

namespace App;

/**
 * Lector mínimo de archivos .env (líneas CLAVE=valor).
 */
final class DotEnv
{
    /**
     * Combina el archivo .env (si existe) con las variables reales del entorno.
     * Las variables del entorno tienen prioridad sobre el archivo.
     *
     * @return array<string, string>
     */
    public static function load(string $path): array
    {
        $values = [];

        if (is_readable($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $values[trim($key)] = trim(trim($value), "\"'");
            }
        }

        return array_merge($values, getenv() ?: []);
    }
}
