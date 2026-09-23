<?php

declare(strict_types=1);

require_once __DIR__ . '/../persistence/productos.persistence.php';

function listar_productos(): array
{
    return list_productos();
}

function obtener_producto_por_id(int $id): ?array
{
    return find_producto_by_id($id);
}

function validar_producto_payload(array $data): array
{
    $nombre = trim((string) ($data['nombre'] ?? ''));
    $precio = $data['precio'] ?? null;
    $stock = $data['stock'] ?? null;
    $errors = [];

    if ($nombre === '') {
        $errors[] = 'Todos los campos son obligatorios.';
    }

    if ($precio === null || $stock === null) {
        $errors[] = 'Todos los campos son obligatorios.';
    }

    if ($precio !== null && !is_numeric($precio)) {
        $errors[] = 'El precio y el stock deben ser numéricos.';
    }

    if ($stock !== null && !is_numeric($stock)) {
        $errors[] = 'El precio y el stock deben ser numéricos.';
    }

    return [
        'errors' => $errors,
        'nombre' => $nombre,
        'precio' => $precio,
        'stock' => $stock,
    ];
}

function crear_producto(array $data): array
{
    $validated = validar_producto_payload($data);

    if ($validated['errors'] !== []) {
        return [
            'errors' => $validated['errors'],
        ];
    }

    $precio = (float) $validated['precio'];
    $stock = (int) $validated['stock'];

    insert_producto($validated['nombre'], $precio, $stock);

    return ['errors' => []];
}

function actualizar_producto(int $id, array $data): array
{
    $validated = validar_producto_payload($data);

    if ($validated['errors'] !== []) {
        return [
            'errors' => $validated['errors'],
        ];
    }

    $producto = find_producto_by_id($id);
    if ($producto === null) {
        return [
            'errors' => ['El producto no existe.'],
        ];
    }

    $precio = (float) $validated['precio'];
    $stock = (int) $validated['stock'];

    update_producto_by_id($id, $validated['nombre'], $precio, $stock);

    return ['errors' => []];
}

function eliminar_producto(int $id): array
{
    $producto = find_producto_by_id($id);

    if ($producto === null) {
        return ['errors' => ['El producto no existe.']];
    }

    delete_producto_by_id($id);

    return ['errors' => []];
}
