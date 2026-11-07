<?php

declare(strict_types=1);

/**
 * Ejecutor de pruebas sin dependencias externas.
 * Uso: composer test   (o: php tests/run.php)
 *
 * Cada archivo tests/*Test.php devuelve un arreglo [nombre => función].
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/support.php';

$passed = 0;
$failed = 0;

foreach (glob(__DIR__ . '/*Test.php') ?: [] as $file) {
    echo basename($file, '.php'), PHP_EOL;

    /** @var array<string, callable> $tests */
    $tests = require $file;

    foreach ($tests as $name => $test) {
        try {
            $test();
            $passed++;
            echo "  ✔ {$name}", PHP_EOL;
        } catch (Throwable $e) {
            $failed++;
            echo "  ✘ {$name}", PHP_EOL, '    ', $e->getMessage(), PHP_EOL;
        }
    }
}

echo PHP_EOL, "{$passed} pasaron, {$failed} fallaron", PHP_EOL;
exit($failed === 0 ? 0 : 1);
