<?php

use RedBeanPHP\R;

class BaseController
{
    public function __construct()
    {
        global $twig;

        $twig->addGlobal('session', $_SESSION);
    }

    public function getBeanById($typeOfBean, $queryStringKey)
    {
        // Allow id to be passed via POST (forms) or GET (query string)
        if (isset($_POST[$queryStringKey])) {
            $id = (int)$_POST[$queryStringKey];
        } elseif (isset($_GET[$queryStringKey])) {
            $id = (int)$_GET[$queryStringKey];
        } else {
            error(400, 'No ' . $typeOfBean . ' id provided');
        }

        $bean = R::load($typeOfBean, $id);

        if (!$bean->id) {
            error(404, ucfirst($typeOfBean) . ' not found');
        }

        return $bean;
    }

    public function authorizeUser()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /user/login");
            exit();
        }
    }

    public function uploadPfp($foundUser)
    {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['profile_picture'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $_SESSION['error'] = 'Ongeldig bestandstype.';
                header('Location: /profile/edit?id=' . $foundUser->id);
                exit();
            }

            $newFilename = 'profile_' . $foundUser->id . '_' . time() . '.' . $ext;
            $uploadDir   = __DIR__ . '/../public/images/';

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
                $foundUser->profile_picture = $newFilename;
            }
        }
    }
}
