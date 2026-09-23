<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/productos.service.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function redirect_to_productos(Request $request, Response $response): Response
{
    return redirectTo('/productos');
}

function list_productos(Request $request, Response $response): Response
{
    global $renderer;

    $productos = listar_productos();

    return view($renderer, $response, '/productos/index.php', [
        'productos' => $productos,
    ]);
}

function show_producto_create_form(Request $request, Response $response): Response
{
    global $renderer;

    return view($renderer, $response, '/productos/create.php');
}

function create_producto(Request $request, Response $response): Response
{
    $data = $request->getParsedBody() ?? [];
    $result = crear_producto($data);

    if ($result['errors'] !== []) {
        $response = new SlimResponse();
        $response->getBody()->write('No se pudo crear el producto.');

        return $response->withStatus(400);
    }

    return redirectTo('/productos');
}

function show_producto(Request $request, Response $response, array $args): Response
{
    global $renderer;

    $id = (int) ($args['id'] ?? 0);
    $producto = obtener_producto_por_id($id);

    if ($producto === null) {
        return view($renderer, $response, '/productos/not_found.php');
    }

    return view($renderer, $response, '/productos/show.php', [
        'producto' => $producto,
    ]);
}

function show_producto_update_form(Request $request, Response $response, array $args): Response
{
    global $renderer;

    $id = (int) ($args['id'] ?? 0);
    $producto = obtener_producto_por_id($id);

    if ($producto === null) {
        return view($renderer, $response, '/productos/not_found.php');
    }

    return view($renderer, $response, '/productos/update.php', [
        'producto' => $producto,
    ]);
}

function update_producto(Request $request, Response $response, array $args): Response
{
    $id = (int) ($args['id'] ?? 0);
    $data = $request->getParsedBody() ?? [];
    $result = actualizar_producto($id, $data);

    if ($result['errors'] !== []) {
        $response = new SlimResponse();
        $response->getBody()->write('No se pudo actualizar el producto.');

        return $response->withStatus(400);
    }

    return redirectTo('/productos');
}

function delete_producto(Request $request, Response $response, array $args): Response
{
    $id = (int) ($args['id'] ?? 0);
    $result = eliminar_producto($id);

    if ($result['errors'] !== []) {
        $response = new SlimResponse();
        $response->getBody()->write('No se pudo eliminar el producto.');

        return $response->withStatus(400);
    }

    return redirectTo('/productos');
}
