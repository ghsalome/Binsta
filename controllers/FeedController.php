<?php

use RedBeanPHP\R;

class FeedController extends BaseController
{
    public function index()
    {
        global $twig;

        $posts = R::findAll('post', 'ORDER BY created_at DESC');

        foreach ($posts as $post) {
            $post->user = R::load('user', $post->user_id);
            $post->comments = R::findAll('comment', 'post_id = ?', [$post->id]);
            $post->likes = R::count('like', 'post_id = ?', [$post->id]);
        }

        if (isset($_SESSION['user_id'])) {
            $user = R::load('user', $_SESSION['user_id']);
        } else {
            $user = null;
        }


        $template = $twig->load('feed/index.twig');
        $template->display([
            'posts' => $posts,
            'user' => $user,
            'error' => $_SESSION['error'] ?? null,
        ]);
    }


    public function show()
    {
        $this->authorizeUser();

        global $twig;

        $post = $this->getBeanById('post', 'id');

        $post->user = R::load('user', $post->user_id);

        $comments = R::findAll('comment', 'post_id = ?', [$post->id]);

        foreach ($comments as $c) {
            $c->user = R::load('user', $c->user_id);
        }

        $likes = R::count('like', 'post_id = ?', [$post->id]);

        $userLiked = (bool) R::findOne('like', 'post_id = ? AND user_id = ?', [
            $post->id,
            $_SESSION['user_id']
        ]);

        $template = $twig->load('feed/show.twig');
        $template->display([
            'post' => $post,
            'comments' => $comments,
            'likes' => $likes,
            'userLiked' => $userLiked,
        ]);
    }

    public function create()
    {
        $this->authorizeUser();

        global $twig;

        $template = $twig->load('feed/create.twig');
        $template->display([]);
    }

    public function createPost()
    {
        $this->authorizeUser();

        $taal = $_POST['language'] ?? 'javascript';
        $code = $_POST['code'];
        $caption = $_POST['caption'];

        $userId = $_SESSION['user_id'];

        $user = R::load('user', $userId);

        if (!$user->id) {
            die("User bestaat niet meer. Log opnieuw in.");
        }

        $post = R::dispense('post');
        $post->user_id = $userId;
        $post->language = $taal;
        $post->code = $code;
        $post->caption = $caption;
        $post->created_at = R::isoDateTime();

        $id = R::store($post);

        header('Location: /feed/show?id=' . $id);
    }

    public function comment()
    {
        $this->authorizeUser();

        $postId = $_POST['post_id'] ?? null;
        $text = trim($_POST['text'] ?? '');
        $userId = $_SESSION['user_id'];

        if (!$postId || $text == '') {
            $_SESSION['error'] = 'Comment mag niet leeg zijn.';
            header('Location: /feed/show?id=' . $postId);
            exit();
        }

        $post = R::load('post', $postId);
        if (!$post->id) {
            die('Post niet gevonden.');
        }

        $comment = R::dispense('comment');
        $comment->post_id = $postId;
        $comment->user_id = $userId;
        $comment->text = $text;
        $comment->created_at = R::isoDateTime();

        R::store($comment);

        header('Location: /feed/show?id=' . $postId);
        exit;
    }

    public function toggle()
    {
        $this->authorizeUser();

        $postId = (int)($_GET['post_id'] ?? $_POST['post_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        if (!$postId) {
            error(400, 'No post id provided');
        }

        $post = R::load('post', $postId);
        if (!$post->id) {
            error(404, 'Post not found');
        }

        $existing = R::findOne('like', 'post_id = ? AND user_id = ?', [$postId, $userId]);

        if ($existing) {
            R::trash($existing);
        } else {
            $like = R::dispense('like');
            $like->post_id = $postId;
            $like->user_id = $userId;
            $like->created_at = R::isoDateTime();
            R::store($like);
        }

        header('Location: /feed/show?id=' . $postId);
        exit;
    }

    public function search()
    {
        global $twig;

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if ($q === '') {
            error(400, "Cannot search for an empty string.");
        }

        $posts = R::find(
            'post',
            'caption LIKE ? OR code LIKE ? OR language LIKE ?',
            ["%$q%", "%$q%", "%$q%"]
        );

        foreach ($posts as $post) {
            $post->user = R::load('user', $post->user_id);
            $post->comments = R::findAll('comment', 'post_id = ?', [$post->id]);
            $post->likes = R::count('like', 'post_id = ?', [$post->id]);
        }

        echo $twig->render('feed/index.twig', [
            'query' => $q,
            'posts' => $posts
        ]);
    }
}
