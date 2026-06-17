<?php

require_once '../vendor/autoload.php';
require_once '../helpers.php';

session_start();

if (function_exists('connectDatabase')) {
    connectDatabase();
}

$loader = new \Twig\Loader\FilesystemLoader('../views');
$twig = new \Twig\Environment($loader, []);
// dit is zodat ik de username van session kan pakken als user ingelogd is
$twig->addGlobal('session', $_SESSION);

$query = $_SERVER['QUERY_STRING'] ?? '';
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

$parts = array_values(array_filter(explode('/', trim($path, '/'))));
parse_str($query, $_GET);

    $controller = $parts[0] ?? 'feed';
    $method = $parts[1] ?? 'index';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $method === 'create') {
    $method = $method . 'Post';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $method === 'edit') {
    $method = $method . 'Post';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $method === 'login') {
    $method = 'loginPost';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $method === 'register') {
    $method = 'registerPost';
}

if (
    !preg_match('/^[a-zA-Z0-9_]+$/', $controller) ||
    !preg_match('/^[a-zA-Z0-9_]+$/', $method)
) {
    http_response_code(400);
    die('Invalid request');
}

$controllerClass = ucfirst($controller) . 'Controller';

if (!class_exists($controllerClass)) {
    error(404, "Controller '$controller' doesn't exist!");
}

$instance = new $controllerClass();

if (!method_exists($instance, $method)) {
    error(404, "Method '$method' doesn't exist!");
}

$instance->$method();
