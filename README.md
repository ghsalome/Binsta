# Binsta

A social platform for sharing code snippets — built from scratch in PHP with no framework. Users can post snippets with syntax highlighting, like and comment on posts, and manage their own profiles.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8+ |
| Templating | Twig 3 |
| ORM | RedBeanPHP |
| Database | MySQL |
| Styling | Tailwind CSS v4 |
| Routing | Custom front controller (`index.php` + `.htaccess`) |

---

## Features

- **Authentication** — Register, login, and logout with bcrypt-hashed passwords
- **Feed** — Browse all code snippets sorted by newest first
- **Posts** — Create posts with a language tag, code body, and caption
- **Likes** — Toggle likes on posts
- **Comments** — Add comments to any post
- **Search** — Search posts by caption, code content, or language
- **Profiles** — View any user's profile and their posts
- **Profile editing** — Update username, bio, display name, password, and profile picture

---

## Project Structure

```
binsta/
├── controllers/
│   ├── BaseController.php       # Shared logic (auth guard, bean loading, file upload)
│   ├── FeedController.php       # Feed, post creation, comments, likes, search
│   ├── ProfileController.php    # User profiles and editing
│   └── UserController.php       # Login, register, logout
├── views/
│   ├── feed/                    # index.twig, show.twig, create.twig
│   ├── profile/                 # profile.twig, edit.twig, index.twig, create.twig
│   ├── users/                   # login.twig, register.twig
│   └── error.twig
├── public/
│   ├── css/
│   │   ├── input.css            # Tailwind entry point
│   │   └── app.css              # Compiled CSS (generated)
│   └── images/                  # Uploaded profile pictures
├── vendor/                      # Composer dependencies
├── helpers.php                  # DB connection, error handler
├── index.php                    # Front controller / router
├── seeder.php                   # Database seeder with sample data
├── composer.json
├── package.json
└── .htaccess                    # Rewrites all requests to index.php
```

---

## Getting Started

### Requirements

- PHP 8.0+
- MySQL
- Composer
- Node.js + npm (for Tailwind)
- Apache with `mod_rewrite` enabled (or equivalent)

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/ghsalome/Binsta.git
cd src
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Install Node dependencies and build CSS**

```bash
npm install
npm run dev
```

**4. Set up the database**

Create a MySQL database named `binsta`:

```sql
CREATE DATABASE binsta;
```

The database credentials are configured in `helpers.php`:

```php
const DB_HOST = 'localhost';
const DB_NAME = 'binsta';
const DB_USER = 'root';
const DB_PASSWORD = '';
```

Update these to match your environment.

**5. (Optional) Seed the database**

Run the seeder from the project root to populate the database with sample users, posts, comments, and likes:

```bash
php seeder.php
```

This creates three test users, each with password `test123`:

| Email | Username |
|---|---|
| alice123@gmail.com | alice |
| bob123@gmail.com | bob |
| charlie123@gmail.com | charlie |

**6. Point your web server at `/public`**

Make sure your Apache virtual host (or equivalent) has its document root set to the `public/` directory. The `.htaccess` file handles routing all requests through `index.php`.

```bash
php -S localhost:8081 -t public
```

---

## Routing

The router is convention-based. URLs follow the pattern:

```
/controller/method?param=value
```

Examples:

| URL | Controller | Method |
|---|---|---|
| `/feed` | FeedController | `index()` |
| `/feed/show?id=1` | FeedController | `show()` |
| `/feed/create` | FeedController | `create()` |
| `/profile/show?id=2` | ProfileController | `show()` |
| `/profile/edit?id=2` | ProfileController | `edit()` |
| `/user/login` | UserController | `login()` |
| `/user/register` | UserController | `register()` |

POST requests to `create` and `edit` routes are automatically dispatched to `createPost()` and `editPost()` respectively.

---

## Tailwind CSS

Tailwind is compiled via the CLI. During development, run:

```bash
npm run dev
```

This watches `public/css/input.css` and outputs the compiled stylesheet to `public/css/app.css`.