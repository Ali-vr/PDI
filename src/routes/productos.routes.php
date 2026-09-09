<?php

/*
|--------------------------------------------------------------------------
| Rutas de productos
|--------------------------------------------------------------------------
| Este archivo contiene todas las operaciones de la entidad productos:
| - Listar
| - Crear
| - Mostrar
| - Actualizar
| - Eliminar
*/

/*
|--------------------------------------------------------------------------
| LISTAR PRODUCTOS
|--------------------------------------------------------------------------
*/


$app->get('/productos', function ($request, $response) use (
    $renderer,
    $database
) {
    $conn = $database->getConnection();

    $stmt = $conn->query(
        'SELECT id, nombre, precio, stock
         FROM productos
         ORDER BY id'
    );

    $productos = $stmt->fetchAll();

    return view(
        $renderer,
        $response,
        '/productos/index.php',
        [
            'productos' => $productos,
        ]
    );
});

/*
|--------------------------------------------------------------------------
| Redirección /productos/
|--------------------------------------------------------------------------
*/

$app->get('/productos/', function ($request, $response) {
    return redirectTo('/productos');
});

/*
|--------------------------------------------------------------------------
| FORMULARIO DE CREACIÓN
|--------------------------------------------------------------------------
*/

$app->get('/productos/create', function ($request, $response) use (
    $renderer
) {
    return view(
        $renderer,
        $response,
        '/productos/create.php'
    );
})->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

/*
|--------------------------------------------------------------------------
| CREAR PRODUCTO
|--------------------------------------------------------------------------
*/

$app->post('/productos', function ($request, $response) use (
    $database
) {
    $data = $request->getParsedBody() ?? [];

    $nombre = trim((string) ($data['nombre'] ?? ''));
    $precio = $data['precio'] ?? null;
    $stock = $data['stock'] ?? null;

    try {
        $database->runTransaction(
            function (\PDO $conn) use (
                $nombre,
                $precio,
                $stock
            ) {
                if (
                    $nombre === '' ||
                    $precio === null ||
                    $stock === null
                ) {
                    throw new \RuntimeException(
                        'Todos los campos son obligatorios.'
                    );
                }

                if (
                    !is_numeric($precio) ||
                    !is_numeric($stock)
                ) {
                    throw new \RuntimeException(
                        'El precio y el stock deben ser numéricos.'
                    );
                }

                $stmt = $conn->prepare(
                    'INSERT INTO productos
                    (nombre, precio, stock)
                    VALUES
                    (:nombre, :precio, :stock)'
                );

                $stmt->execute([
                    ':nombre' => $nombre,
                    ':precio' => $precio,
                    ':stock' => $stock,
                ]);

                return $stmt->rowCount();
            }
        );

        return redirectTo('/productos');
    } catch (\Throwable $e) {
        $response = new SlimResponse();

        $response->getBody()->write(
            'No se pudo crear el producto.'
        );

        return $response->withStatus(400);
    }
})->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

/*
|--------------------------------------------------------------------------
| MOSTRAR PRODUCTO
|--------------------------------------------------------------------------
*/

$app->get('/productos/{id}', function (
    $request,
    $response,
    $args
) use (
    $renderer,
    $database
) {
    $id = (int) $args['id'];

    $conn = $database->getConnection();

    $stmt = $conn->prepare(
        'SELECT id, nombre, precio, stock
         FROM productos
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $id
    ]);

    $producto = $stmt->fetch();

    if (!$producto) {
        return view(
            $renderer,
            $response,
            '/productos/not_found.php'
        );
    }

    return view(
        $renderer,
        $response,
        '/productos/show.php',
        [
            'producto' => $producto,
        ]
    );
});

/*
|--------------------------------------------------------------------------
| FORMULARIO DE ACTUALIZACIÓN
|--------------------------------------------------------------------------
*/

$app->get('/productos/update/{id}', function (
    $request,
    $response,
    $args
) use (
    $renderer,
    $database
) {
    $id = (int) $args['id'];

    $conn = $database->getConnection();

    $stmt = $conn->prepare(
        'SELECT id, nombre, precio, stock
         FROM productos
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $id
    ]);

    $producto = $stmt->fetch();

    if (!$producto) {
        return view(
            $renderer,
            $response,
            '/productos/not_found.php'
        );
    }

    return view(
        $renderer,
        $response,
        '/productos/update.php',
        [
            'producto' => $producto,
        ]
    );
})->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

/*
|--------------------------------------------------------------------------
| ACTUALIZAR PRODUCTO
|--------------------------------------------------------------------------
*/

$app->put('/productos/{id}', function (
    $request,
    $response,
    $args
) use (
    $database
) {
    $id = (int) $args['id'];

    $data = $request->getParsedBody() ?? [];

    $nombre = trim((string) ($data['nombre'] ?? ''));
    $precio = $data['precio'] ?? null;
    $stock = $data['stock'] ?? null;

    try {
        $database->runTransaction(
            function (\PDO $conn) use (
                $id,
                $nombre,
                $precio,
                $stock
            ) {
                if (
                    $nombre === '' ||
                    $precio === null ||
                    $stock === null
                ) {
                    throw new \RuntimeException(
                        'Todos los campos son obligatorios.'
                    );
                }

                if (
                    !is_numeric($precio) ||
                    !is_numeric($stock)
                ) {
                    throw new \RuntimeException(
                        'El precio y el stock deben ser numéricos.'
                    );
                }

                $check = $conn->prepare(
                    'SELECT id
                     FROM productos
                     WHERE id = :id'
                );

                $check->execute([
                    ':id' => $id
                ]);

                if ($check->fetch() === false) {
                    throw new \RuntimeException(
                        'El producto no existe.'
                    );
                }

                $stmt = $conn->prepare(
                    'UPDATE productos
                     SET nombre = :nombre,
                         precio = :precio,
                         stock = :stock
                     WHERE id = :id'
                );

                $stmt->execute([
                    ':nombre' => $nombre,
                    ':precio' => $precio,
                    ':stock' => $stock,
                    ':id' => $id,
                ]);

                return $stmt->rowCount();
            }
        );

        return redirectTo('/productos');
    } catch (\Throwable $e) {
        $response = new SlimResponse();

        $response->getBody()->write(
            'No se pudo actualizar el producto.'
        );

        return $response->withStatus(400);
    }
})->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});

/*
|--------------------------------------------------------------------------
| ELIMINAR PRODUCTO
|--------------------------------------------------------------------------
*/

$app->delete('/productos/{id}', function (
    $request,
    $response,
    $args
) use (
    $database
) {
    $id = (int) $args['id'];

    try {
        $database->runTransaction(
            function (\PDO $conn) use ($id) {
                $check = $conn->prepare(
                    'SELECT id
                     FROM productos
                     WHERE id = :id'
                );

                $check->execute([
                    ':id' => $id
                ]);

                if ($check->fetch() === false) {
                    throw new \RuntimeException(
                        'El producto no existe.'
                    );
                }

                $stmt = $conn->prepare(
                    'DELETE FROM productos
                     WHERE id = :id'
                );

                $stmt->execute([
                    ':id' => $id
                ]);

                return $stmt->rowCount();
            }
        );

        return redirectTo('/productos');
    } catch (\Throwable $e) {
        $response = new SlimResponse();

        $response->getBody()->write(
            'No se pudo eliminar el producto.'
        );

        return $response->withStatus(400);
    }
})->add(function (
    Request $request,
    RequestHandler $handler
): Response {
    return authMiddleware($request, $handler);
});
