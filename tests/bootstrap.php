<?php

declare(strict_types=1);

/**
 * Bootstrap dos testes.
 *
 * Objetivos:
 *  - garantir ambiente determinístico: banco SEMPRE indisponível, então
 *    os repositórios exercitam o fallback demo documentado;
 *  - iniciar a sessão em CLI antes de qualquer saída, para o singleton
 *    App\Core\Session reaproveitar a sessão ativa sem warnings de header;
 *  - registrar o autoload do namespace Tests\ -> pasta tests/.
 */

error_reporting(E_ALL & ~E_DEPRECATED);

// Sessão de CLI sem cookies, iniciada antes de o runner imprimir qualquer coisa.
if (PHP_SAPI === 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_cookies', '0');
    ini_set('session.cache_limiter', '');
    @session_start();
}
$_SESSION = [];

// Força modo demo/fallback: aponta o MySQL para uma porta fechada.
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_PORT'] = '65534';
$_SERVER['DB_HOST'] = '127.0.0.1';
$_SERVER['DB_PORT'] = '65534';

require dirname(__DIR__) . '/app/bootstrap.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'Tests\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
