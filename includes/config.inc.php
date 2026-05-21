<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

date_default_timezone_set('Europe/Budapest');

$ablakcim = [
    'cim' => 'Konyhatár',
    'motto' => 'Receptgyűjtemény és közösségi főzős oldal',
];

$fejlec = [
    'cim' => 'Konyhatár',
    'motto' => 'PHP front-controller mintára épített beadandó',
];

$lablec = [
    'copyright' => 'Copyright ' . date('Y') . '.',
    'ceg' => 'Konyhatár - Web-programozás 1 beadandó',
];

$oldalak = [
    '/' => ['fajl' => 'cimlap', 'szoveg' => 'Főoldal', 'menun' => [1, 1]],
    'kepek' => ['fajl' => 'kepek', 'szoveg' => 'Képek', 'menun' => [1, 1]],
    'kapcsolat' => ['fajl' => 'kapcsolat', 'szoveg' => 'Kapcsolat', 'menun' => [1, 1]],
    'uzenetek' => ['fajl' => 'uzenetek', 'szoveg' => 'Üzenetek', 'menun' => [0, 1]],
    'crud' => ['fajl' => 'crud', 'szoveg' => 'CRUD', 'menun' => [1, 1]],
    'crud/uj' => ['fajl' => 'crud_form', 'szoveg' => '', 'menun' => [0, 0]],
    'belepes' => ['fajl' => 'belepes', 'szoveg' => 'Belépés', 'menun' => [1, 0]],
    'kilepes' => ['fajl' => 'kilepes', 'szoveg' => 'Kilépés', 'menun' => [0, 1]],
    'belep' => ['fajl' => 'belep', 'szoveg' => '', 'menun' => [0, 0]],
    'regisztral' => ['fajl' => 'regisztral', 'szoveg' => '', 'menun' => [0, 0]],
    'kepfeltoltes' => ['fajl' => 'kepfeltoltes', 'szoveg' => '', 'menun' => [0, 0]],
    'kapcsolat_kuld' => ['fajl' => 'kapcsolat_kuld', 'szoveg' => '', 'menun' => [0, 0]],
    'crudmentes' => ['fajl' => 'crudmentes', 'szoveg' => '', 'menun' => [0, 0]],
];

$hiba_oldal = ['fajl' => '404', 'szoveg' => 'A keresett oldal nem található!'];

function app_base_path(): string
{
    return dirname(__DIR__);
}

function app_base_url(): string
{
    $dir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    if ($dir === '.' || $dir === '\\') {
        return '';
    }

    return rtrim(str_replace('\\', '/', $dir), '/');
}

function app_url(string $route = ''): string
{
    $base = rtrim(app_base_url(), '/');
    if ($route === '' || $route === '/') {
        return $base === '' ? '/' : $base . '/';
    }

    $normalized = '/' . ltrim($route, '/');
    return ($base === '' ? '' : $base) . $normalized;
}

function app_driver(): string
{
    $hostName = $_SERVER['HTTP_HOST'] ?? '';
    return getenv('DB_DRIVER') ?: (str_contains($hostName, 'rbreceptek.nhely.hu') ? 'mysql' : 'sqlite');
}

function app_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (app_driver() === 'mysql') {
        $host = getenv('DB_HOST') ?: 'localhost';
        $database = getenv('DB_NAME') ?: 'rbreceptek';
        $username = getenv('DB_USER') ?: 'rbreceptek';
        $password = getenv('DB_PASS') ?: '4R5t6z7u8i9o.';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $database, $charset),
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } else {
        $pdo = new PDO('sqlite:' . app_base_path() . '/storage/data/app.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function app_ensure_csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }

    return $_SESSION['csrf_token'];
}

function app_csrf_token(): string
{
    return app_ensure_csrf_token();
}

function app_guard_csrf(): void
{
    $token = (string) ($_POST['_token'] ?? '');
    if (!hash_equals(app_csrf_token(), $token)) {
        http_response_code(419);
        exit('Érvénytelen kérés.');
    }
}

function app_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function app_pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function app_set_old(array $old): void
{
    $_SESSION['old'] = $old;
}

function app_old(): array
{
    $old = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);
    return $old;
}

function app_set_errors(array $errors): void
{
    $_SESSION['errors'] = $errors;
}

function app_errors(): array
{
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['errors']);
    return $errors;
}

function app_redirect(string $route = '/'): never
{
    header('Location: ' . app_url($route));
    exit;
}

function app_current_user(): ?array
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return null;
    }

    $statement = app_pdo()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $userId]);
    return $statement->fetch() ?: null;
}

function app_is_logged_in(): bool
{
    return app_current_user() !== null;
}

function app_require_login(): void
{
    if (!app_is_logged_in()) {
        app_flash('error', 'Ehhez a funkcióhoz be kell jelentkezni.');
        app_redirect('belepes');
    }
}

function app_login_session(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['login'] = $user['username'];
    $_SESSION['csn'] = $user['family_name'];
    $_SESSION['un'] = $user['given_name'];
}

function app_logout_session(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function app_resolve_route(string $oldal, array $oldalak, array $hibaOldal): array
{
    if (isset($oldalak[$oldal])) {
        $keres = $oldalak[$oldal];
        $keres['url'] = $oldal;
        $keres['aktiv'] = $oldal;
        return $keres;
    }

    $dynamicRoutes = [
        '#^kapcsolat/siker/(\d+)$#' => ['fajl' => 'kapcsolat_siker', 'aktiv' => 'kapcsolat', 'param' => 'id'],
        '#^crud/szerkesztes/(\d+)$#' => ['fajl' => 'crud_form', 'aktiv' => 'crud', 'param' => 'id'],
        '#^crud/frissites/(\d+)$#' => ['fajl' => 'crudfrissites', 'aktiv' => 'crud', 'param' => 'id'],
        '#^crud/torles/(\d+)$#' => ['fajl' => 'crudtorles', 'aktiv' => 'crud', 'param' => 'id'],
        '#^keptorles/(\d+)$#' => ['fajl' => 'keptorles', 'aktiv' => 'kepek', 'param' => 'id'],
    ];

    foreach ($dynamicRoutes as $pattern => $definition) {
        if (preg_match($pattern, $oldal, $matches) === 1) {
            $keres = $definition;
            $keres['url'] = $oldal;
            $keres['params'] = [$definition['param'] => (int) $matches[1]];
            return $keres;
        }
    }

    $hibaOldal['url'] = $oldal;
    $hibaOldal['aktiv'] = '';
    return $hibaOldal;
}

function app_featured_recipes(): array
{
    return app_pdo()->query(
        'SELECT recipes.id, recipes.name, recipes.summary, recipes.prep_minutes, recipes.difficulty,
                categories.name AS category_name, recipes.featured_image
         FROM recipes
         JOIN categories ON categories.id = recipes.category_id
         ORDER BY recipes.published_at DESC
         LIMIT 6'
    )->fetchAll();
}

function app_stats(): array
{
    return [
        'recept' => (int) app_pdo()->query('SELECT COUNT(*) FROM recipes')->fetchColumn(),
        'hozzavalo' => (int) app_pdo()->query('SELECT COUNT(*) FROM ingredients')->fetchColumn(),
        'uzenet' => (int) app_pdo()->query('SELECT COUNT(*) FROM messages')->fetchColumn(),
    ];
}

function app_gallery_images(): array
{
    return app_pdo()->query(
        'SELECT gallery_images.*, users.family_name, users.given_name
         FROM gallery_images
         LEFT JOIN users ON users.id = gallery_images.uploaded_by_user_id
         ORDER BY gallery_images.created_at DESC'
    )->fetchAll();
}

function app_messages(): array
{
    app_require_login();
    return app_pdo()->query(
        'SELECT messages.*, users.family_name, users.given_name, users.username
         FROM messages
         LEFT JOIN users ON users.id = messages.user_id
         ORDER BY messages.created_at DESC'
    )->fetchAll();
}

function app_message(int $id): ?array
{
    $statement = app_pdo()->prepare(
        'SELECT messages.*, users.family_name, users.given_name, users.username
         FROM messages
         LEFT JOIN users ON users.id = messages.user_id
         WHERE messages.id = :id'
    );
    $statement->execute(['id' => $id]);
    return $statement->fetch() ?: null;
}

function app_crud_recipes(): array
{
    return app_pdo()->query(
        'SELECT recipes.id, recipes.name, recipes.summary, recipes.prep_minutes, recipes.difficulty,
                recipes.published_at, categories.name AS category_name,
                COUNT(recipe_ingredients.ingredient_id) AS ingredient_count
         FROM recipes
         JOIN categories ON categories.id = recipes.category_id
         LEFT JOIN recipe_ingredients ON recipe_ingredients.recipe_id = recipes.id
         GROUP BY recipes.id
         ORDER BY recipes.name ASC'
    )->fetchAll();
}

function app_categories(): array
{
    return app_pdo()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}

function app_recipe(?int $id = null): array
{
    $recipe = [
        'name' => '',
        'category_id' => '',
        'summary' => '',
        'difficulty' => 'kozepes',
        'prep_minutes' => 30,
        'featured_image' => '',
    ];

    if ($id !== null) {
        $statement = app_pdo()->prepare('SELECT * FROM recipes WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $recipe = $statement->fetch() ?: $recipe;
    }

    return $recipe;
}

function app_recipe_payload(): array
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

function app_validate_recipe(array $payload): array
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
