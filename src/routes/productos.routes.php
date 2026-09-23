<?php

use Slim\App;

/** @var App $app */

require_once __DIR__ . '/../controllers/productos.controller.php';

$app->get('/productos', 'list_productos');
$app->get('/productos/', 'redirect_to_productos');

$app->get('/productos/create', 'show_producto_create_form')->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

$app->post('/productos', 'create_producto')->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

$app->get('/productos/{id}', 'show_producto');

$app->get('/productos/update/{id}', 'show_producto_update_form')->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

$app->put('/productos/{id}', 'update_producto')->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

$app->delete('/productos/{id}', 'delete_producto')->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});
