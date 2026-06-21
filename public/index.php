<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Config\AppLogger;
use Core\ErrorPage;
use Core\View;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$dotenv->required(['APP_URL', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME']);
$dotenv->ifPresent('REMEMBER_ME_ENABLED')->isBoolean();
$dotenv->ifPresent('REMEMBER_ME_LIFETIME')->isInteger();

View::setBasePath(__DIR__ . '/../resources/views');

use Core\Router;
use Core\Security;

Security::sendSecurityHeaders();

$projectRoot   = __DIR__ . '/..';
$publicBaseUrl = rtrim($_ENV['APP_URL'], '/') . '/public/';
$logger        = AppLogger::getInstance();

register_shutdown_function(static function () use ($projectRoot, $publicBaseUrl, $logger): void {
    $lastError = error_get_last();
    if ($lastError === null) {
        return;
    }

    $fatalErrorTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array((int)$lastError['type'], $fatalErrorTypes, true)) {
        return;
    }

    $logger->critical('Fatal error', [
        'message' => $lastError['message'],
        'file'    => $lastError['file'],
        'line'    => $lastError['line'],
    ]);
    if (!headers_sent()) {
        ErrorPage::render($projectRoot, $publicBaseUrl, 500);
    }
});

try {
    $container = new \Core\Container();
    $containerLoader = require __DIR__ . '/../config/container.php';
    if (is_callable($containerLoader)) {
        $containerLoader($container);
    }

    $eventsRegistrar = require __DIR__ . '/../config/events.php';
    if (is_callable($eventsRegistrar)) {
        $eventsRegistrar($container, $container->resolve(\App\Domain\Contracts\EventDispatcherInterface::class));
    }

    $router = new Router($projectRoot, 'public', $publicBaseUrl);
    $routesRegistrar = require __DIR__ . '/../routes/web.php';
    if (is_callable($routesRegistrar)) {
        $routesRegistrar($router, $container);
    }

    $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/index.php';
    $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

    $router->dispatch($requestUri, $scriptName, $requestMethod);
} catch (\Throwable $exception) {
    $logger->error('Unhandled exception', [
        'message' => $exception->getMessage(),
        'file'    => $exception->getFile(),
        'line'    => $exception->getLine(),
        'trace'   => $exception->getTraceAsString(),
    ]);
    ErrorPage::render($projectRoot, $publicBaseUrl, 500);
}
