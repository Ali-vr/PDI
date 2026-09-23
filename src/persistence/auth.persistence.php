<?php

declare(strict_types=1);

function user_exists_by_email(string $email): bool
{
    global $database;

    $conn = $database->getConnection();
    $stmt = $conn->prepare(
        'SELECT id
         FROM usuarios
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute([':email' => $email]);

    return $stmt->fetch() !== false;
}

function create_user(string $nombre, string $email, string $password): void
{
    global $database;

    $conn = $database->getConnection();
    $stmt = $conn->prepare(
        'INSERT INTO usuarios (nombre, email, password)
         VALUES (:nombre, :email, :password)'
    );

    $stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':password' => $password,
    ]);
}

function find_user_by_email(string $email): ?array
{
    global $database;

    $conn = $database->getConnection();
    $stmt = $conn->prepare(
        'SELECT id, nombre, password
         FROM usuarios
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    return $user === false ? null : $user;
}
