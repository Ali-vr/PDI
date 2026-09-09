<?php

/*
|--------------------------------------------------------------------------
| Rutas de autenticación
|--------------------------------------------------------------------------
| Este archivo contiene exclusivamente las rutas relacionadas con:
| - Registro de usuarios
| - Inicio de sesión
| - Compatibilidad con /formulario/register
*/



function renderRegisterView(
    PhpRenderer $renderer,
    Response $response,
    string $template,
    array $data = [],
    array $errors = []
): Response {
    return view($renderer, $response, $template, [
        'errors' => $errors,
        'data' => $data,
    ]);
}

function handleRegisterRequest(
    Request $request,
    Response $response,
    PhpRenderer $renderer,
    Database $database,
    string $template
): Response {
    $method = $request->getMethod();

    if ($method === 'GET') {
        return renderRegisterView(
            $renderer,
            $response,
            $template,
            [],
            []
        );
    }

    $body = $request->getParsedBody() ?? [];

    $nombre = trim((string) ($body['nombre'] ?? ''));
    $email = trim((string) ($body['email'] ?? ''));
    $password = (string) ($body['password'] ?? '');
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

    if ($errors === []) {
        try {
            $conn = $database->getConnection();

            $check = $conn->prepare(
                'SELECT id FROM usuarios WHERE email = :email LIMIT 1'
            );

            $check->execute([
                ':email' => $email
            ]);

            if ($check->fetch() !== false) {
                $errors[] = 'Ya existe un usuario registrado con ese email.';
            }
        } catch (\Throwable $e) {
            $errors[] = 'No se pudo validar la existencia del usuario.';
        }
    }

    if ($errors !== []) {
        return renderRegisterView(
            $renderer,
            $response,
            $template,
            [
                'nombre' => $nombre,
                'email' => $email,
            ],
            $errors
        );
    }

    try {
        $conn = $database->getConnection();

        $stmt = $conn->prepare(
            'INSERT INTO usuarios (nombre, email, password)
             VALUES (:nombre, :email, :password)'
        );

        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return redirectTo('/auth/login');
    } catch (\Throwable $e) {
        return renderRegisterView(
            $renderer,
            $response,
            $template,
            [
                'nombre' => $nombre,
                'email' => $email,
            ],
            ['No se pudo completar el registro. Intenta nuevamente.']
        );
    }
}

function handleLoginRequest(
    Request $request,
    Response $response,
    PhpRenderer $renderer,
    Database $database,
    string $template
): Response {
    $method = $request->getMethod();

    if ($method === 'GET') {
        return view(
            $renderer,
            $response,
            $template,
            ['errors' => []]
        );
    }

    $body = $request->getParsedBody() ?? [];

    $email = trim((string) ($body['email'] ?? ''));
    $password = (string) ($body['password'] ?? '');
    $errors = [];

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email es obligatorio y debe ser válido.';
    }

    if ($password === '') {
        $errors[] = 'La contraseña es obligatoria.';
    }

    if ($errors === []) {
        try {
            $conn = $database->getConnection();

            $stmt = $conn->prepare(
                'SELECT id, nombre, password
                 FROM usuarios
                 WHERE email = :email
                 LIMIT 1'
            );

            $stmt->execute([
                ':email' => $email
            ]);

            $user = $stmt->fetch();

            if (
                $user !== false &&
                password_verify($password, $user['password'])
            ) {
                ensureSession();

                session_regenerate_id(true);

                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];

                return redirectTo('/productos');
            }

            $errors[] = 'Credenciales inválidas.';
        } catch (\Throwable $e) {
            $errors[] = 'No se pudo iniciar sesión en este momento.';
        }
    }

    return view(
        $renderer,
        $response,
        $template,
        [
            'errors' => $errors,
            'data' => [
                'email' => $email
            ],
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Registro
|--------------------------------------------------------------------------
*/

$app->get('/auth/register', function ($request, $response) use (
    $renderer,
    $database
) {
    return handleRegisterRequest(
        $request,
        $response,
        $renderer,
        $database,
        '/auth/register.php'
    );
});

$app->post('/auth/register', function ($request, $response) use (
    $renderer,
    $database
) {
    return handleRegisterRequest(
        $request,
        $response,
        $renderer,
        $database,
        '/auth/register.php'
    );
});

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

$app->get('/auth/login', function ($request, $response) use (
    $renderer,
    $database
) {
    return handleLoginRequest(
        $request,
        $response,
        $renderer,
        $database,
        '/auth/login.php'
    );
});

$app->post('/auth/login', function ($request, $response) use (
    $renderer,
    $database
) {
    return handleLoginRequest(
        $request,
        $response,
        $renderer,
        $database,
        '/auth/login.php'
    );
});

/*
|--------------------------------------------------------------------------
| Compatibilidad con la ruta anterior
|--------------------------------------------------------------------------
*/

$app->get('/formulario/register', function ($request, $response) {
    return redirectTo('/auth/register');
});

$app->post('/formulario/register', function ($request, $response) {
    return redirectTo('/auth/register');
});
