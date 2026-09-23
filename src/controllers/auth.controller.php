<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/auth.service.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\PhpRenderer;

function show_register_form(Request $request, Response $response): Response
{
    global $renderer;

    return view($renderer, $response, '/auth/register.php', [
        'errors' => [],
        'data' => [],
    ]);
}

function handle_register(Request $request, Response $response): Response
{
    global $renderer;

    $body = $request->getParsedBody() ?? [];
    $data = [
        'nombre' => trim((string) ($body['nombre'] ?? '')),
        'email' => trim((string) ($body['email'] ?? '')),
        'password' => (string) ($body['password'] ?? ''),
    ];

    try {
        $result = register_user($data);

        if ($result['errors'] !== []) {
            return view($renderer, $response, '/auth/register.php', [
                'errors' => $result['errors'],
                'data' => [
                    'nombre' => $data['nombre'],
                    'email' => $data['email'],
                ],
            ]);
        }

        return redirectTo('/auth/login');
    } catch (\Throwable $e) {
        return view($renderer, $response, '/auth/register.php', [
            'errors' => ['No se pudo completar el registro. Intenta nuevamente.'],
            'data' => [
                'nombre' => $data['nombre'],
                'email' => $data['email'],
            ],
        ]);
    }
}

function show_login_form(Request $request, Response $response): Response
{
    global $renderer;

    return view($renderer, $response, '/auth/login.php', [
        'errors' => [],
        'data' => [],
    ]);
}

function handle_login(Request $request, Response $response): Response
{
    global $renderer;

    $body = $request->getParsedBody() ?? [];
    $data = [
        'email' => trim((string) ($body['email'] ?? '')),
        'password' => (string) ($body['password'] ?? ''),
    ];

    $result = login_user($data);

    if ($result['errors'] !== []) {
        return view($renderer, $response, '/auth/login.php', [
            'errors' => $result['errors'],
            'data' => [
                'email' => $data['email'],
            ],
        ]);
    }

    ensureSession();
    session_regenerate_id(true);

    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['user_nombre'] = $result['user']['nombre'];

    return redirectTo('/productos');
}

function redirect_to_auth_register(Request $request, Response $response): Response
{
    return redirectTo('/auth/register');
}
