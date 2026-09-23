<?php

declare(strict_types=1);

require_once __DIR__ . '/../persistence/auth.persistence.php';

function register_user(array $data): array
{
    $nombre = trim((string) ($data['nombre'] ?? ''));
    $email = trim((string) ($data['email'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    $errors = [];

    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email es obligatorio y debe ser válido.';
    }

    if ($password === '' || strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($errors === [] && user_exists_by_email($email)) {
        $errors[] = 'Ya existe un usuario registrado con ese email.';
    }

    if ($errors !== []) {
        return [
            'errors' => $errors,
            'user' => null,
        ];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    create_user($nombre, $email, $hashedPassword);

    return [
        'errors' => [],
        'user' => [
            'nombre' => $nombre,
            'email' => $email,
        ],
    ];
}

function login_user(array $data): array
{
    $email = trim((string) ($data['email'] ?? ''));
    $password = (string) ($data['password'] ?? '');
    $errors = [];

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email es obligatorio y debe ser válido.';
    }

    if ($password === '') {
        $errors[] = 'La contraseña es obligatoria.';
    }

    if ($errors !== []) {
        return [
            'errors' => $errors,
            'user' => null,
        ];
    }

    $user = find_user_by_email($email);

    if ($user === null || !password_verify($password, $user['password'])) {
        return [
            'errors' => ['Credenciales inválidas.'],
            'user' => null,
        ];
    }

    return [
        'errors' => [],
        'user' => [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
        ],
    ];
}
