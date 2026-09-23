<?php

declare(strict_types=1);

function list_productos(): array
{
    global $database;

    $conn = $database->getConnection();
    $stmt = $conn->query(
        'SELECT id, nombre, precio, stock
         FROM productos
         ORDER BY id'
    );

    return $stmt->fetchAll();
}

function find_producto_by_id(int $id): ?array
{
    global $database;

    $conn = $database->getConnection();
    $stmt = $conn->prepare(
        'SELECT id, nombre, precio, stock
         FROM productos
         WHERE id = :id'
    );

    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch();

    return $producto === false ? null : $producto;
}

function insert_producto(string $nombre, float $precio, int $stock): void
{
    global $database;

    $database->runTransaction(
        function (\PDO $conn) use ($nombre, $precio, $stock) {
            $stmt = $conn->prepare(
                'INSERT INTO productos (nombre, precio, stock)
                 VALUES (:nombre, :precio, :stock)'
            );

            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => $precio,
                ':stock' => $stock,
            ]);
        }
    );
}

function update_producto_by_id(int $id, string $nombre, float $precio, int $stock): void
{
    global $database;

    $database->runTransaction(
        function (\PDO $conn) use ($id, $nombre, $precio, $stock) {
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
        }
    );
}

function delete_producto_by_id(int $id): void
{
    global $database;

    $database->runTransaction(
        function (\PDO $conn) use ($id) {
            $stmt = $conn->prepare(
                'DELETE FROM productos
                 WHERE id = :id'
            );

            $stmt->execute([':id' => $id]);
        }
    );
}
