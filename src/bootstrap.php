<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/database/database.php';

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\PhpRenderer;

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$env = $_ENV['APP_ENV'] ?? 'prod';
$allowedEnvs = ['dev', 'prod'];

if (!in_array($env, $allowedEnvs, true)) {
    throw new \RuntimeException("APP_ENV inválido: {$env}");
}

$debug = $env === 'dev';

$app = AppFactory::create();
$app->add(new MethodOverrideMiddleware());

$database = new Database();

$renderer = new PhpRenderer(
    templatePath: __DIR__ . '/views',
    attributes: ['title' => 'PDI | Slim Template 2026'],
);

function ensureSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function redirectTo(string $path, int $status = 302): Response
{
    return (new SlimResponse())
        ->withHeader('Location', $path)
        ->withStatus($status);
}

function authMiddleware(Request $request, RequestHandler $handler): Response
{
    ensureSession();

    $userId = $_SESSION['user_id'] ?? null;

    if ($userId === null || $userId === '') {
        return redirectTo('/auth/login');
    }

    $request = $request->withAttribute('user_id', $userId);

    return $handler->handle($request);
}

function logMiddleware(Request $request, RequestHandler $handler): Response
{
    $start = microtime(true);
    $response = $handler->handle($request);

    $elapsedMs = (microtime(true) - $start) * 1000;
    $timestamp = date('Y-m-d H:i:s');
    $method = $request->getMethod();
    $path = $request->getUri()->getPath();
    $statusCode = $response->getStatusCode();

    $logLine = sprintf(
        '[%s] %s %s %d %.2f ms',
        $timestamp,
        $method,
        $path,
        $statusCode,
        $elapsedMs
    );

    error_log($logLine);

    $logDir = __DIR__ . '/../logs';

    if (!is_dir($logDir) && !mkdir($logDir, 0777, true) && !is_dir($logDir)) {
        throw new \RuntimeException('No se pudo crear el directorio de logs.');
    }

    file_put_contents(
        $logDir . '/app.log',
        $logLine . PHP_EOL,
        FILE_APPEND
    );

    return $response;
}

$app->add(function (Request $request, RequestHandler $handler): Response {
    return logMiddleware($request, $handler);
});

/*
|--------------------------------------------------------------------------
| Rutas
|--------------------------------------------------------------------------
| Cada entidad tiene su propio archivo de rutas.
| Esto mantiene bootstrap.php enfocado en la configuración de la aplicación.
*/

require __DIR__ . '/routes/auth.routes.php';
require __DIR__ . '/routes/productos.routes.php';

$app->addErrorMiddleware($debug, true, true);

return $app;
