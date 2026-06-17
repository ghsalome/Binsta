<?php

use RedBeanPHP\R;

// Database configuration
const DB_HOST = 'localhost';
const DB_NAME = 'binsta';
const DB_USER = 'root';
const DB_PASSWORD = '';

function connectDatabase()
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    R::setup($dsn, DB_USER, DB_PASSWORD);
}

function displayWelcomePage(string $name, array $fruits)
{
    global $twig;

    $template = $twig->load('welcome.twig');

    $template->display([
        'name' => $name,
        'fruits' => $fruits,
    ]);
}

function error($errorNumber, $errorMessage)
{
    global $twig;

    http_response_code($errorNumber);

    $template = $twig->load('error.twig');

    $template->display([
        'errorNumber' => $errorNumber,
        'errorMessage' => $errorMessage,
    ]);

    die();
}
