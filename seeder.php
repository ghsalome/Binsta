<?php

use RedBeanPHP\R;

require_once 'vendor/autoload.php';
require_once 'helpers.php';

connectDatabase();

R::nuke();
R::wipe('user');
R::wipe('post');
R::wipe('comment');
R::wipe('like');

// ----------------------
// USERS
// ----------------------
$users = [
    [
        'email' => 'alice123@gmail.com',
        'username' => 'alice',
        'password' => password_hash('test123', PASSWORD_BCRYPT),
        'name' => 'Alice Dev',
        'bio' => 'Full‑stack developer & coffee lover.',
        'profile_picture' => 'default1.png'
    ],
    [
        'email' => 'bob123@gmail.com',
        'username' => 'bob',
        'password' => password_hash('test123', PASSWORD_BCRYPT),
        'name' => 'Bob Codes',
        'bio' => 'Backend wizard.',
        'profile_picture' => 'default2.png'
    ],
    [
        'email' => 'charlie123@gmail.com',
        'username' => 'charlie',
        'password' => password_hash('test123', PASSWORD_BCRYPT),
        'name' => 'Charlie Script',
        'bio' => 'JavaScript enjoyer.',
        'profile_picture' => 'default3.png'
    ],
];

$userBeans = [];

foreach ($users as $u) {
    $bean = R::dispense('user');
    foreach ($u as $k => $v) {
        $bean->$k = $v;
    }
    R::store($bean);
    $userBeans[] = $bean;
}

// ----------------------
// POSTS
// ----------------------
$languages = ['javascript', 'php', 'python', 'html'];

$captions = [
    "Even wat code magie ✨",
    "Kleine snippet, grote impact.",
    "Vandaag weer iets nieuws geleerd!",
    "Code is poëzie in nullen en enen.",
    "Debuggen? Nee joh, werkt in één keer 😎",
    "Simpel maar effectief.",
    "Mijn brein na 3 koppen koffie ☕💻",
    "Kijk mam, ik programmeer!",
    "Dit moest ik even delen.",
    "Kleine snippet, grote vibes."
];


$exampleCode = [
    'javascript' => 'const greet = name => `Hello ${name}`;',
    'php' => '<?php echo "Hello World"; ?>',
    'python' => 'print("Hello World")',
    'html' => '<h1>Hello World</h1>'
];


for ($i = 0; $i < 10; $i++) {
    $post = R::dispense('post');
    $post->user_id = $userBeans[array_rand($userBeans)]->id;
    $post->language = $languages[array_rand($languages)];
    $post->code = $exampleCode[$post->language];
    $post->caption = $captions[array_rand($captions)];
    $post->created_at = date('Y-m-d H:i:s', time() - rand(0, 50000));
    R::store($post);
}

// ----------------------
// COMMENTS
// ----------------------
$allPosts = R::findAll('post');

foreach ($allPosts as $post) {
    if (rand(0, 1)) {
        $comment = R::dispense('comment');
        $comment->user_id = $userBeans[array_rand($userBeans)]->id;
        $comment->post_id = $post->id;
        $comment->text = "Nice snippet!";
        $comment->created_at = date('Y-m-d H:i:s');
        R::store($comment);
    }
}

// ----------------------
// LIKES
// ----------------------
foreach ($allPosts as $post) {
    foreach ($userBeans as $u) {
        if (rand(0, 1)) {
            $like = R::dispense('like');
            $like->user_id = $u->id;
            $like->post_id = $post->id;
            R::store($like);
        }
    }
}

echo "Seeder completed!" . PHP_EOL;
