<?php

declare(strict_types=1);

final class App
{
    public function __construct(
        private PDO $pdo,
        private array $config,
    ) {
    }

    public function run(): void
    {
        $this->ensureCsrfToken();
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $route = $this->currentRoute();

        if ($route === 'kilepes') {
            $this->logout();
            return;
        }

        if ($route === 'belepes' && $method === 'POST') {
            $action = $_POST['form_type'] ?? '';
            if ($action === 'login') {
                $this->login();
                return;
            }
            if ($action === 'register') {
                $this->register();
                return;
            }
        }

        if ($route === 'kepek' && $method === 'POST') {
            $this->uploadImage();
            return;
        }

        if (preg_match('#^kepek/torles/(\d+)$#', $route, $matches) === 1 && $method === 'POST') {
            $this->deleteImage((int) $matches[1]);
            return;
        }

        if ($route === 'kapcsolat' && $method === 'POST') {
            $this->storeMessage();
            return;
        }

        if ($route === 'crud/uj' && $method === 'POST') {
            $this->createRecipe();
            return;
        }

        if (preg_match('#^crud/szerkesztes/(\d+)$#', $route, $matches) === 1 && $method === 'POST') {
            $this->updateRecipe((int) $matches[1]);
            return;
        }

        if (preg_match('#^crud/torles/(\d+)$#', $route, $matches) === 1 && $method === 'POST') {
            $this->deleteRecipe((int) $matches[1]);
            return;
        }

        $pageData = match (true) {
            $route === '' => $this->homePage(),
            $route === 'kepek' => $this->galleryPage(),
            $route === 'kapcsolat' => $this->contactPage(),
            preg_match('#^kapcsolat/siker/(\d+)$#', $route, $matches) === 1 => $this->contactSuccessPage((int) $matches[1]),
            $route === 'uzenetek' => $this->messagesPage(),
            $route === 'crud' => $this->crudListPage(),
            $route === 'crud/uj' => $this->crudFormPage(),
            preg_match('#^crud/szerkesztes/(\d+)$#', $route, $matches) === 1 => $this->crudFormPage((int) $matches[1]),
            $route === 'belepes' => $this->authPage(),
            default => $this->notFoundPage(),
        };

        $this->render($pageData);
    }

    private function render(array $pageData): void
    {
        $currentUser = $this->currentUser();
        $flash = $this->pullFlash();
        $csrf = $_SESSION['csrf_token'];
        $baseUrl = $this->baseUrl();
        $config = $this->config;

        extract($pageData, EXTR_SKIP);
        require $this->config['base_path'] . '/views/layout.php';
    }

    private function homePage(): array
    {
        $featuredRecipes = $this->pdo->query(
            'SELECT recipes.id, recipes.name, recipes.summary, recipes.prep_minutes, recipes.difficulty,
                    categories.name AS category_name, recipes.featured_image
             FROM recipes
             JOIN categories ON categories.id = recipes.category_id
             ORDER BY recipes.published_at DESC
             LIMIT 6'
        )->fetchAll();

        $stats = [
            'recept' => (int) $this->pdo->query('SELECT COUNT(*) FROM recipes')->fetchColumn(),
            'hozzavalo' => (int) $this->pdo->query('SELECT COUNT(*) FROM ingredients')->fetchColumn(),
            'uzenet' => (int) $this->pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
        ];

        return [
            'pageTitle' => 'Főoldal',
            'page' => 'home',
            'featuredRecipes' => $featuredRecipes,
            'stats' => $stats,
        ];
    }

    private function galleryPage(): array
    {
        $images = $this->pdo->query(
            'SELECT gallery_images.*, users.family_name, users.given_name
             FROM gallery_images
             LEFT JOIN users ON users.id = gallery_images.uploaded_by_user_id
             ORDER BY gallery_images.created_at DESC'
        )->fetchAll();

        return [
            'pageTitle' => 'Képek',
            'page' => 'gallery',
            'images' => $images,
            'old' => $_SESSION['old'] ?? [],
            'errors' => $_SESSION['errors'] ?? [],
        ];
    }

    private function contactPage(): array
    {
        return [
            'pageTitle' => 'Kapcsolat',
            'page' => 'contact',
            'old' => $_SESSION['old'] ?? [],
            'errors' => $_SESSION['errors'] ?? [],
        ];
    }

    private function contactSuccessPage(int $messageId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT messages.*, users.family_name, users.given_name, users.username
             FROM messages
             LEFT JOIN users ON users.id = messages.user_id
             WHERE messages.id = :id'
        );
        $statement->execute(['id' => $messageId]);
        $message = $statement->fetch();

        if (!$message) {
            return $this->notFoundPage();
        }

        return [
            'pageTitle' => 'Elküldött üzenet',
            'page' => 'contact-success',
            'message' => $message,
        ];
    }

    private function messagesPage(): array
    {
        $this->requireLogin();

        $messages = $this->pdo->query(
            'SELECT messages.*, users.family_name, users.given_name, users.username
             FROM messages
             LEFT JOIN users ON users.id = messages.user_id
             ORDER BY messages.created_at DESC'
        )->fetchAll();

        return [
            'pageTitle' => 'Üzenetek',
            'page' => 'messages',
            'messages' => $messages,
        ];
    }

    private function crudListPage(): array
    {
        $recipes = $this->pdo->query(
            'SELECT recipes.id, recipes.name, recipes.summary, recipes.prep_minutes, recipes.difficulty,
                    recipes.published_at, categories.name AS category_name,
                    COUNT(recipe_ingredients.ingredient_id) AS ingredient_count
             FROM recipes
             JOIN categories ON categories.id = recipes.category_id
             LEFT JOIN recipe_ingredients ON recipe_ingredients.recipe_id = recipes.id
             GROUP BY recipes.id
             ORDER BY recipes.name ASC'
        )->fetchAll();

        return [
            'pageTitle' => 'CRUD',
            'page' => 'crud-list',
            'recipes' => $recipes,
        ];
    }

    private function crudFormPage(?int $recipeId = null): array
    {
        $recipe = [
            'name' => '',
            'category_id' => '',
            'summary' => '',
            'difficulty' => 'kozepes',
            'prep_minutes' => 30,
        ];

        if ($recipeId !== null) {
            $statement = $this->pdo->prepare('SELECT * FROM recipes WHERE id = :id');
            $statement->execute(['id' => $recipeId]);
            $recipe = $statement->fetch() ?: $recipe;
        }

        return [
            'pageTitle' => $recipeId === null ? 'Új recept' : 'Recept szerkesztése',
            'page' => 'crud-form',
            'recipeId' => $recipeId,
            'recipe' => array_merge($recipe, $_SESSION['old'] ?? []),
            'categories' => $this->pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(),
            'errors' => $_SESSION['errors'] ?? [],
        ];
    }

    private function authPage(): array
    {
        return [
            'pageTitle' => 'Belépés és regisztráció',
            'page' => 'auth',
            'old' => $_SESSION['old'] ?? [],
            'errors' => $_SESSION['errors'] ?? [],
        ];
    }

    private function notFoundPage(): array
    {
        http_response_code(404);

        return [
            'pageTitle' => 'Az oldal nem található',
            'page' => 'not-found',
        ];
    }

    private function uploadImage(): void
    {
        $this->requireLogin();
        $this->guardCsrf();

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $file = $_FILES['image'] ?? null;
        $errors = [];

        if ($title === '' || mb_strlen($title) < 3) {
            $errors['title'] = 'A cím legalább 3 karakter legyen.';
        }

        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors['image'] = 'Válassz egy feltöltendő képet.';
        } else {
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];
            $mime = mime_content_type($file['tmp_name']);
            if (!isset($allowed[$mime])) {
                $errors['image'] = 'Csak JPG, PNG, WEBP vagy GIF fájl tölthető fel.';
            }
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['title' => $title, 'description' => $description];
            $this->redirect('kepek');
        }

        $extension = $allowed[$mime];
        $filename = sprintf('%s.%s', bin2hex(random_bytes(10)), $extension);
        $target = $this->config['base_path'] . '/storage/uploads/' . $filename;
        move_uploaded_file($file['tmp_name'], $target);

        $statement = $this->pdo->prepare(
            'INSERT INTO gallery_images (title, description, filename, external_url, uploaded_by_user_id, created_at)
             VALUES (:title, :description, :filename, NULL, :user_id, :created_at)'
        );
        $statement->execute([
            'title' => $title,
            'description' => $description,
            'filename' => $filename,
            'user_id' => $this->currentUser()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'A kép sikeresen feltöltve.');
        $this->redirect('kepek');
    }

    private function storeMessage(): void
    {
        $this->guardCsrf();

        $user = $this->currentUser();
        $senderName = trim((string) ($_POST['sender_name'] ?? ''));
        $senderEmail = trim((string) ($_POST['sender_email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $errors = [];

        if ($senderName === '' || mb_strlen($senderName) < 3) {
            $errors['sender_name'] = 'Adj meg legalább 3 karakteres nevet.';
        }
        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['sender_email'] = 'Érvényes e-mail címet adj meg.';
        }
        if ($subject === '' || mb_strlen($subject) < 5) {
            $errors['subject'] = 'A tárgy legalább 5 karakter legyen.';
        }
        if ($body === '' || mb_strlen($body) < 20) {
            $errors['body'] = 'Az üzenet legalább 20 karakter legyen.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = compact('senderName', 'senderEmail', 'subject', 'body');
            $this->redirect('kapcsolat');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO messages (sender_name, sender_email, subject, body, user_id, created_at)
             VALUES (:sender_name, :sender_email, :subject, :body, :user_id, :created_at)'
        );
        $statement->execute([
            'sender_name' => $senderName,
            'sender_email' => $senderEmail,
            'subject' => $subject,
            'body' => $body,
            'user_id' => $user['id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $messageId = (int) $this->pdo->lastInsertId();
        $this->flash('success', 'Az üzenetet elmentettük.');
        $this->redirect('kapcsolat/siker/' . $messageId);
    }

    private function deleteImage(int $imageId): void
    {
        $this->requireLogin();
        $this->guardCsrf();

        $statement = $this->pdo->prepare(
            'SELECT id, filename, uploaded_by_user_id
             FROM gallery_images
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $imageId]);
        $image = $statement->fetch();

        if (!$image) {
            $this->flash('error', 'A kép nem található.');
            $this->redirect('kepek');
        }

        $currentUser = $this->currentUser();
        if (!$currentUser || (int) $image['uploaded_by_user_id'] !== (int) $currentUser['id']) {
            $this->flash('error', 'Csak a saját feltöltött képed törölheted.');
            $this->redirect('kepek');
        }

        $deleteStatement = $this->pdo->prepare('DELETE FROM gallery_images WHERE id = :id');
        $deleteStatement->execute(['id' => $imageId]);

        $filename = (string) ($image['filename'] ?? '');
        if ($filename !== '') {
            $path = $this->config['base_path'] . '/storage/uploads/' . $filename;
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->flash('success', 'A saját feltöltött kép törölve lett.');
        $this->redirect('kepek');
    }

    private function createRecipe(): void
    {
        $this->guardCsrf();
        $payload = $this->recipePayload();
        $errors = $this->validateRecipe($payload);

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $payload;
            $this->redirect('crud/uj');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO recipes (name, category_id, created_at, published_at, summary, difficulty, prep_minutes, featured_image)
             VALUES (:name, :category_id, :created_at, :published_at, :summary, :difficulty, :prep_minutes, :featured_image)'
        );
        $statement->execute([
            'name' => $payload['name'],
            'category_id' => $payload['category_id'],
            'created_at' => date('Y-m-d'),
            'published_at' => date('Y-m-d'),
            'summary' => $payload['summary'],
            'difficulty' => $payload['difficulty'],
            'prep_minutes' => $payload['prep_minutes'],
            'featured_image' => $payload['featured_image'],
        ]);

        $this->flash('success', 'Az új recept bekerült az adatbázisba.');
        $this->redirect('crud');
    }

    private function updateRecipe(int $recipeId): void
    {
        $this->guardCsrf();
        $payload = $this->recipePayload();
        $errors = $this->validateRecipe($payload);

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $payload;
            $this->redirect('crud/szerkesztes/' . $recipeId);
        }

        $statement = $this->pdo->prepare(
            'UPDATE recipes
             SET name = :name,
                 category_id = :category_id,
                 summary = :summary,
                 difficulty = :difficulty,
                 prep_minutes = :prep_minutes,
                 featured_image = :featured_image
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $recipeId,
            'name' => $payload['name'],
            'category_id' => $payload['category_id'],
            'summary' => $payload['summary'],
            'difficulty' => $payload['difficulty'],
            'prep_minutes' => $payload['prep_minutes'],
            'featured_image' => $payload['featured_image'],
        ]);

        $this->flash('success', 'A recept adatai frissültek.');
        $this->redirect('crud');
    }

    private function deleteRecipe(int $recipeId): void
    {
        $this->guardCsrf();

        $statement = $this->pdo->prepare('DELETE FROM recipes WHERE id = :id');
        $statement->execute(['id' => $recipeId]);

        $this->flash('success', 'A recept törölve lett.');
        $this->redirect('crud');
    }

    private function login(): void
    {
        $this->guardCsrf();
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $statement = $this->pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['errors'] = ['login' => 'Hibás felhasználónév vagy jelszó.'];
            $_SESSION['old'] = ['username' => $username];
            $this->redirect('belepes');
        }

        $_SESSION['user_id'] = $user['id'];
        $this->flash('success', 'Sikeres belépés.');
        $this->redirect('');
    }

    private function register(): void
    {
        $this->guardCsrf();

        $familyName = trim((string) ($_POST['family_name'] ?? ''));
        $givenName = trim((string) ($_POST['given_name'] ?? ''));
        $username = trim((string) ($_POST['register_username'] ?? ''));
        $email = trim((string) ($_POST['register_email'] ?? ''));
        $password = (string) ($_POST['register_password'] ?? '');
        $errors = [];

        if ($familyName === '' || $givenName === '') {
            $errors['name'] = 'A családi név és az utónév kitöltése kötelező.';
        }
        if ($username === '' || mb_strlen($username) < 4) {
            $errors['register_username'] = 'A login név legalább 4 karakter legyen.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['register_email'] = 'Érvényes e-mail címet adj meg.';
        }
        if (mb_strlen($password) < 8) {
            $errors['register_password'] = 'A jelszó legalább 8 karakter legyen.';
        }

        $exists = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username OR email = :email');
        $exists->execute(['username' => $username, 'email' => $email]);
        if ((int) $exists->fetchColumn() > 0) {
            $errors['register_username'] = 'Ez a login név vagy e-mail már foglalt.';
        }

        if ($errors !== []) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = compact('familyName', 'givenName', 'username', 'email');
            $this->redirect('belepes');
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO users (family_name, given_name, username, email, password_hash, created_at)
             VALUES (:family_name, :given_name, :username, :email, :password_hash, :created_at)'
        );
        $statement->execute([
            'family_name' => $familyName,
            'given_name' => $givenName,
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Sikeres regisztráció. Most már be tudsz lépni.');
        $this->redirect('belepes');
    }

    private function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        $this->flash('success', 'Sikeres kilépés.');
        $this->redirect('');
    }

    private function recipePayload(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
            'difficulty' => trim((string) ($_POST['difficulty'] ?? 'kozepes')),
            'prep_minutes' => (int) ($_POST['prep_minutes'] ?? 0),
            'featured_image' => trim((string) ($_POST['featured_image'] ?? '')),
        ];
    }

    private function validateRecipe(array $payload): array
    {
        $errors = [];

        if ($payload['name'] === '' || mb_strlen($payload['name']) < 3) {
            $errors['name'] = 'A recept neve legalább 3 karakter legyen.';
        }
        if ($payload['category_id'] < 1) {
            $errors['category_id'] = 'Válassz kategóriát.';
        }
        if ($payload['summary'] === '' || mb_strlen($payload['summary']) < 15) {
            $errors['summary'] = 'A leírás legalább 15 karakter legyen.';
        }
        if (!in_array($payload['difficulty'], ['konnyu', 'kozepes', 'halado'], true)) {
            $errors['difficulty'] = 'Érvénytelen nehézségi szint.';
        }
        if ($payload['prep_minutes'] < 5 || $payload['prep_minutes'] > 300) {
            $errors['prep_minutes'] = 'Az elkészítési idő 5 és 300 perc között legyen.';
        }

        return $errors;
    }

    private function currentUser(): ?array
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        $statement = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);

        return $statement->fetch() ?: null;
    }

    private function requireLogin(): void
    {
        if ($this->currentUser() === null) {
            $this->flash('error', 'Ehhez a funkcióhoz be kell jelentkezni.');
            $this->redirect('belepes');
        }
    }

    private function currentRoute(): string
    {
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        $scriptDir = $scriptDir === '\\' ? '/' : rtrim(str_replace('\\', '/', $scriptDir), '/');

        if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($requestPath, $scriptDir)) {
            $requestPath = substr($requestPath, strlen($scriptDir));
        }

        return trim($requestPath, '/');
    }

    private function ensureCsrfToken(): void
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
        }
    }

    private function guardCsrf(): void
    {
        $token = (string) ($_POST['_token'] ?? '');
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(419);
            exit('Érvénytelen kérés.');
        }
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    private function pullFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash'], $_SESSION['errors'], $_SESSION['old']);
        return $flash;
    }

    private function redirect(string $route): never
    {
        $target = rtrim($this->baseUrl(), '/');
        $location = $route === '' ? ($target === '' ? '/' : $target . '/') : $target . '/' . ltrim($route, '/');
        header('Location: ' . $location);
        exit;
    }

    private function baseUrl(): string
    {
        $dir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        if ($dir === '.' || $dir === '\\') {
            return '';
        }

        return rtrim(str_replace('\\', '/', $dir), '/');
    }
}
