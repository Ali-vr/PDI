<?php

use Slim\App;

/** @var App $app */

require_once __DIR__ . '/../controllers/auth.controller.php';

$app->get('/auth/register', 'show_register_form');
$app->post('/auth/register', 'handle_register');

$app->get('/auth/login', 'show_login_form');
$app->post('/auth/login', 'handle_login');

$app->get('/formulario/register', 'redirect_to_auth_register');
$app->post('/formulario/register', 'redirect_to_auth_register');
