<?php

use RedBeanPHP\R;

class ProfileController extends BaseController
{
    public function show()
    {
        global $twig;

        // Haal de user op via ID uit de querystring
        $foundUser = $this->getBeanById('user', 'id');

        // Haal alle posts/snippets van deze user op
        $posts = R::find('post', 'user_id = ?', [$foundUser->id]);

        $template = $twig->load('profile/profile.twig');
        $template->display([
            'user' => $foundUser,
            'posts' => $posts,
        ]);
    }

    public function index()
    {
        global $twig;

        // Alle users tonen
        $users = R::findAll('user');

        $template = $twig->load("profile/index.twig");
        $template->display([
            'users' => $users,
        ]);
    }

    public function create()
    {
        $this->authorizeUser();
        global $twig;

        $template = $twig->load('profile/create.twig');
        $template->display([]);
    }

    public function createPost()
    {
        $this->authorizeUser();

        $user = R::dispense('user');
        $user->username = $_POST['username'] ?? null;
        $user->bio = $_POST['bio'] ?? null;

        R::store($user);

        header('Location: /profile/show?id=' . $user->id);
        exit();
    }

    public function edit()
    {
        $this->authorizeUser();
        global $twig;

        $foundUser = $this->getBeanById('user', 'id');

        if ($_SESSION['user_id'] !== $foundUser->id) {
            $_SESSION['error'] = 'Je hebt geen toestemming om deze gebruiker te bewerken.';
            header('Location: /feed/index');
            exit();
        }

        $template = $twig->load('profile/edit.twig');
        $template->display([
            'id' => $foundUser->id,
            'username' => $foundUser->username,
            'user' => $foundUser,
        ]);

        unset($_SESSION['error']);
    }

    public function editPost()
    {
        $this->authorizeUser();

        $foundUser = $this->getBeanById('user', 'id');
        $current = $_POST['password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($_SESSION['user_id'] !== $foundUser->id) {
            $_SESSION['error'] = 'Je hebt geen toestemming om deze gebruiker te bewerken.';
            header('Location: /feed/index');
            exit();
        }


        if (!empty($current)) {
            if (!password_verify($current, $foundUser->password)) {
                $_SESSION['error'] = 'Wachtwoord klopt niet!';
                header('Location: /');
                exit();
            }
        }


        if (!empty($new)) {
            if ($new !== $confirm) {
                $_SESSION['error'] = 'Nieuwe wachtwoorden komen niet overeen.';
                header('Location: /');
                exit();
            }

            $foundUser->password = password_hash($new, PASSWORD_BCRYPT);
        }

        $foundUser->username = $_POST['username'] ?? $foundUser->username;
        $foundUser->bio = $_POST['bio'] ?? $foundUser->bio;
        $foundUser->name = $_POST['name'] ?? $foundUser->name;

        $this->uploadPfp($foundUser);

        R::store($foundUser);

        $_SESSION['username'] = $foundUser->username;
        $_SESSION['profile_picture'] = $foundUser->profile_picture;

        header('Location: /profile/show?id=' . $foundUser->id);
        exit();
    }
}
