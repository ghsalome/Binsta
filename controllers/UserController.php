<?php

use RedBeanPHP\R;

class UserController extends BaseController
{
    public function login()
    {
        global $twig;

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $template = $twig->load('users/login.twig');
        $template->display(['error' => $error]);
    }

    public function loginPost()
    {
        $user = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $foundUser = R::findOne('user', 'email = ?', [$user]);

        if ($foundUser && password_verify($password, $foundUser->password)) {
            $_SESSION['user_id'] = $foundUser->id;
            $_SESSION['email'] = $foundUser->email;
            $_SESSION['username'] = $foundUser->username;
            $_SESSION['profile_picture'] = $foundUser->profile_picture;
            header('Location: /feed');
            exit;
        } else {
            $_SESSION['error'] = 'Ongeldige Email of wachtwoord';
            header('Location: /user/login');
            exit;
        }
    }

    public function register()
    {
        global $twig;

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $template = $twig->load('users/register.twig');
        $template->display(['error' => $error]);
    }

    public function registerPost()
    {
        $email = $_POST['email'] ?? '';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // check als alle velden zijn ingevuld
        if ($email === '' || $username === '' || $password === '' || $confirm === '') {
            $_SESSION['error'] = 'Alle velden zijn verplicht.';
            header('Location: user/register');
            exit;
        }

        // check als password overeenkomt
        if ($password !== $confirm) {
            $_SESSION['error'] = 'Wachtwoorden komen niet overeen.';
            header('Location: /user/register');
            exit;
        }

        $existing = R::findOne('user', 'email = ?', [$email]);

        // check als de email al bestaat
        if ($existing) {
            $_SESSION['error'] = 'Email bestaat al.';
            header('Location: /user/register');
            exit;
        }

        // maak user aan
        $user = R::dispense('user');
        $user->email = $email;
        $user->username = $username;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->profile_picture = 'placeholder.jpg';
        R::store($user);

        // log gelijk in
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['username'] = $user->username;
        $_SESSION['profile_picture'] = $user->profile_picture;

        header("Location: /feed/index");
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: /user/login');
        exit;
    }
}
