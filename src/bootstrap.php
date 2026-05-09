<?php

declare(strict_types=1);

require __DIR__ . '/App.php';

function build_app(string $basePath): App
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    date_default_timezone_set('Europe/Budapest');

    $hostName = $_SERVER['HTTP_HOST'] ?? '';
    $driver = getenv('DB_DRIVER') ?: (str_contains($hostName, 'rbreceptek.nhely.hu') ? 'mysql' : 'sqlite');

    if ($driver === 'mysql') {
        $host = getenv('DB_HOST') ?: 'localhost';
        $database = getenv('DB_NAME') ?: 'rbreceptek';
        $username = getenv('DB_USER') ?: 'rbreceptek';
        $password = getenv('DB_PASS') ?: '4R5t6z7u8i9o.';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $database, $charset),
            $username,
            $password
        );
    } else {
        $dbPath = $basePath . '/storage/data/app.db';
        $pdo = new PDO('sqlite:' . $dbPath);
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $config = [
        'app_name' => 'Konyhatár',
        'base_path' => $basePath,
        'map_embed' => 'https://www.google.com/maps?q=Budapest%20R%C3%A1day%20utca%2013&output=embed',
        'youtube_embed' => 'https://www.youtube.com/embed/xPPLbEFbCAo?si=yb9qQ-4NQ-rjH2Mo',
        'local_video' => 'public/assets/media/konyhatar-intro.mp4',
        'gallery_seed_urls' => [
            'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=900&q=80',
        ],
    ];

    return new App($pdo, $config);
}
