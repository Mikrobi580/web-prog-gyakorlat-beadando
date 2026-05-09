<?php

declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

$app = build_app(__DIR__);
$app->run();
