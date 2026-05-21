<?php if (file_exists(app_base_path() . '/logicals/' . $keres['fajl'] . '.php')) { include app_base_path() . '/logicals/' . $keres['fajl'] . '.php'; } ?>
<?php
$currentUser = app_current_user();
$flash = app_pull_flash();
$errors = app_errors();
$old = app_old();
$csrf = app_csrf_token();
$aktivRoute = $keres['aktiv'] ?? ($keres['url'] ?? '/');
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($ablakcim['cim']) . (isset($ablakcim['motto']) ? ' | ' . htmlspecialchars($ablakcim['motto']) : '') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('/public/assets/css/style.css')) ?>" type="text/css">
</head>
<body>
    <header class="site-header">
        <div class="shell">
            <div class="brand-row">
                <a class="brand" href="<?= htmlspecialchars(app_url('/')) ?>">
                    <span class="brand-mark">KT</span>
                    <span>
                        <strong><?= htmlspecialchars($fejlec['cim']) ?></strong>
                        <small><?= htmlspecialchars($fejlec['motto']) ?></small>
                    </span>
                </a>
                <p class="user-badge">
                    <?php if (isset($_SESSION['login'])): ?>
                        Bejelentkezett: <?= htmlspecialchars($_SESSION['csn'] . ' ' . $_SESSION['un'] . ' (' . $_SESSION['login'] . ')') ?>
                    <?php else: ?>
                        Jelenleg vendégként böngészel.
                    <?php endif; ?>
                </p>
            </div>
            <nav class="main-nav">
                <?php foreach ($oldalak as $url => $oldal): ?>
                    <?php
                    $isVisible = !isset($_SESSION['login']) ? (bool) $oldal['menun'][0] : (bool) $oldal['menun'][1];
                    if (!$isVisible || $oldal['szoveg'] === '') {
                        continue;
                    }
                    $isActive = $url === $aktivRoute || ($aktivRoute === 'crud' && str_starts_with($url, 'crud') && $url !== 'crudmentes');
                    ?>
                    <a href="<?= htmlspecialchars(app_url($url)) ?>" class="<?= $isActive ? 'active' : '' ?>">
                        <?= htmlspecialchars($oldal['szoveg']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>

    <main class="shell main-content">
        <?php if ($flash): ?>
            <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <?php include app_base_path() . '/templates/pages/' . $keres['fajl'] . '.tpl.php'; ?>
    </main>

    <footer class="site-footer">
        <div class="shell">
            <p>
                <?= htmlspecialchars($lablec['copyright']) ?>
                <?= htmlspecialchars($lablec['ceg']) ?>
            </p>
        </div>
    </footer>
    <script src="<?= htmlspecialchars(app_url('/public/assets/js/app.js')) ?>"></script>
</body>
</html>
