<?php
$appName = $config['app_name'];
$userDisplay = $currentUser ? sprintf(
    'Bejelentkezett: %s %s (%s)',
    htmlspecialchars($currentUser['family_name']),
    htmlspecialchars($currentUser['given_name']),
    htmlspecialchars($currentUser['username'])
) : 'Jelenleg vendégként böngészel.';

$navItems = [
    ['label' => 'Főoldal', 'route' => ''],
    ['label' => 'Képek', 'route' => 'kepek'],
    ['label' => 'Kapcsolat', 'route' => 'kapcsolat'],
    ['label' => 'CRUD', 'route' => 'crud'],
];

if ($currentUser) {
    $navItems[] = ['label' => 'Üzenetek', 'route' => 'uzenetek'];
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars($appName) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/public/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="shell">
            <div class="brand-row">
                <a class="brand" href="<?= htmlspecialchars($baseUrl) ?>/">
                    <span class="brand-mark">KT</span>
                    <span>
                        <strong><?= htmlspecialchars($appName) ?></strong>
                        <small>Receptgyűjtemény és közösségi főzős oldal</small>
                    </span>
                </a>
                <p class="user-badge"><?= $userDisplay ?></p>
            </div>
            <nav class="main-nav">
                <?php foreach ($navItems as $item): ?>
                    <?php
                    $targetRoute = $item['route'] === '' ? 'home' : $item['route'];
                    $isActive = $page === $targetRoute || ($targetRoute === 'crud' && str_starts_with($page, 'crud'));
                    ?>
                    <a href="<?= htmlspecialchars($baseUrl . '/' . ltrim($item['route'], '/')) ?>" class="<?= $isActive ? 'active' : '' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
                <?php if (!$currentUser): ?>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/belepes" class="<?= $page === 'auth' ? 'active' : '' ?>">Belépés</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/kilepes">Kilépés</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="shell main-content">
        <?php if ($flash): ?>
            <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <?php if ($page === 'home'): ?>
            <section class="hero">
                <div>
                    <p class="eyebrow">Otthoni konyha, adatbázis-alapon</p>
                    <h1>Receptgyűjteményből épített, modern PHP webalkalmazás.</h1>
                    <p class="hero-copy">A Konyhatár a választott recept-adatbázist látványos, reszponzív felülettel, galériával, kapcsolatoldallal, üzenetkezeléssel és teljes CRUD modullal egészíti ki.</p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="<?= htmlspecialchars($baseUrl) ?>/crud">Receptadatbázis megnyitása</a>
                        <a class="button button-secondary" href="<?= htmlspecialchars($baseUrl) ?>/kapcsolat">Üzenet küldése</a>
                    </div>
                </div>
                <div class="hero-panel">
                    <div class="stats-grid">
                        <article><strong><?= (int) $stats['recept'] ?></strong><span>recept</span></article>
                        <article><strong><?= (int) $stats['hozzavalo'] ?></strong><span>hozzávaló</span></article>
                        <article><strong><?= (int) $stats['uzenet'] ?></strong><span>üzenet</span></article>
                    </div>
                    <p class="panel-note">A kezdőadatok a beadandóhoz megadott recept-adatbázisból épülnek fel.</p>
                </div>
            </section>

            <section class="section-grid">
                <div class="content-card">
                    <h2>Kiemelt receptek</h2>
                    <div class="recipe-grid">
                        <?php foreach ($featuredRecipes as $recipe): ?>
                            <article class="recipe-card">
                                <img src="<?= htmlspecialchars($recipe['featured_image']) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>">
                                <div>
                                    <p class="tag"><?= htmlspecialchars($recipe['category_name']) ?> • <?= htmlspecialchars($recipe['difficulty']) ?></p>
                                    <h3><?= htmlspecialchars(mb_convert_case($recipe['name'], MB_CASE_TITLE, 'UTF-8')) ?></h3>
                                    <p><?= htmlspecialchars($recipe['summary']) ?></p>
                                    <small><?= (int) $recipe['prep_minutes'] ?> perc</small>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <aside class="content-card spotlight">
                    <h2>Miért hasznos?</h2>
                    <ul class="plain-list">
                        <li>Front-controller alapú útvonalkezelés</li>
                        <li>Belépés, regisztráció és kilépés</li>
                        <li>Kapcsolat űrlap kliens- és szerveroldali validálással</li>
                        <li>Képfeltöltés csak bejelentkezett felhasználóknak</li>
                        <li>Receptkezelő CRUD modul adatbázissal</li>
                    </ul>
                </aside>
            </section>

            <section class="media-grid">
                <article class="content-card">
                    <h2>Saját videó</h2>
                    <video controls preload="metadata" class="media-frame">
                        <source src="<?= htmlspecialchars($baseUrl . '/' . $config['local_video']) ?>" type="video/mp4">
                    </video>
                </article>
                <article class="content-card">
                    <h2>YouTube videó</h2>
                    <iframe class="media-frame" src="<?= htmlspecialchars($config['youtube_embed']) ?>" title="YouTube video" allowfullscreen></iframe>
                </article>
            </section>

            <section class="content-card map-card">
                <div>
                    <h2>Hol található a bemutató konyha?</h2>
                    <p>A felület mintacímként egy budapesti gasztropontot mutat. A beágyazott Google térkép a beadandó egyik kötelező elemét valósítja meg.</p>
                </div>
                <iframe src="<?= htmlspecialchars($config['map_embed']) ?>" class="map-frame" loading="lazy" title="Google térkép"></iframe>
            </section>
        <?php elseif ($page === 'gallery'): ?>
            <section class="page-head">
                <h1>Képgaléria</h1>
                <p>Receptekhez kapcsolódó hangulatképek és felhasználói feltöltések.</p>
            </section>
            <?php if ($currentUser): ?>
                <section class="content-card form-card">
                    <h2>Új kép feltöltése</h2>
                    <form method="post" enctype="multipart/form-data" novalidate data-validate>
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                        <div class="form-grid">
                            <label>
                                <span>Cím</span>
                                <input type="text" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>" data-required data-minlength="3">
                                <small class="error-text"><?= htmlspecialchars($errors['title'] ?? '') ?></small>
                            </label>
                            <label>
                                <span>Kép</span>
                                <input type="file" name="image" accept="image/*" data-required>
                                <small class="error-text"><?= htmlspecialchars($errors['image'] ?? '') ?></small>
                            </label>
                        </div>
                        <label>
                            <span>Leírás</span>
                            <textarea name="description" rows="4"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        </label>
                        <button class="button button-primary" type="submit">Kép feltöltése</button>
                    </form>
                </section>
            <?php else: ?>
                <div class="flash flash-info">Képet csak bejelentkezett felhasználó tölthet fel.</div>
            <?php endif; ?>

            <section class="gallery-grid">
                <?php foreach ($images as $image): ?>
                    <?php
                    $imageSrc = $image['filename']
                        ? $baseUrl . '/storage/uploads/' . $image['filename']
                        : $image['external_url'];
                    $author = $image['family_name']
                        ? $image['family_name'] . ' ' . $image['given_name']
                        : 'Konyhatár szerkesztőség';
                    $canDelete = $currentUser && (int) ($image['uploaded_by_user_id'] ?? 0) === (int) $currentUser['id'];
                    ?>
                    <article class="gallery-card">
                        <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                        <div>
                            <h3><?= htmlspecialchars($image['title']) ?></h3>
                            <p><?= htmlspecialchars($image['description']) ?></p>
                            <small><?= htmlspecialchars($author) ?> • <?= htmlspecialchars($image['created_at']) ?></small>
                            <?php if ($canDelete): ?>
                                <form method="post" action="<?= htmlspecialchars($baseUrl . '/kepek/torles/' . $image['id']) ?>" class="inline-form">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <button type="submit" class="button-link danger-link" onclick="return confirm('Biztosan törölni szeretnéd ezt a képet?');">Saját kép törlése</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php elseif ($page === 'contact'): ?>
            <section class="page-head">
                <h1>Kapcsolat</h1>
                <p>Küldj üzenetet az oldal tulajdonosának. Az üzenet külön oldalon megjelenik és adatbázisba is mentésre kerül.</p>
            </section>
            <section class="content-card form-card">
                <form method="post" novalidate data-validate>
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="form-grid">
                        <label>
                            <span>Név</span>
                            <?php $defaultSenderName = $currentUser ? $currentUser['family_name'] . ' ' . $currentUser['given_name'] : ''; ?>
                            <input type="text" name="sender_name" value="<?= htmlspecialchars($old['senderName'] ?? $defaultSenderName) ?>" data-required data-minlength="3">
                            <small class="error-text"><?= htmlspecialchars($errors['sender_name'] ?? '') ?></small>
                        </label>
                        <label>
                            <span>E-mail</span>
                            <input type="email" name="sender_email" value="<?= htmlspecialchars($old['senderEmail'] ?? ($currentUser['email'] ?? '')) ?>" data-required data-email>
                            <small class="error-text"><?= htmlspecialchars($errors['sender_email'] ?? '') ?></small>
                        </label>
                    </div>
                    <label>
                        <span>Tárgy</span>
                        <input type="text" name="subject" value="<?= htmlspecialchars($old['subject'] ?? '') ?>" data-required data-minlength="5">
                        <small class="error-text"><?= htmlspecialchars($errors['subject'] ?? '') ?></small>
                    </label>
                    <label>
                        <span>Üzenet</span>
                        <textarea name="body" rows="7" data-required data-minlength="20"><?= htmlspecialchars($old['body'] ?? '') ?></textarea>
                        <small class="error-text"><?= htmlspecialchars($errors['body'] ?? '') ?></small>
                    </label>
                    <button class="button button-primary" type="submit">Üzenet elküldése</button>
                </form>
            </section>
        <?php elseif ($page === 'contact-success'): ?>
            <section class="content-card">
                <h1>Elküldött üzenet</h1>
                <p>Az űrlap adatai mentésre kerültek, és ez az oldal külön tartalomként megjeleníti őket.</p>
                <dl class="message-detail">
                    <div><dt>Név</dt><dd><?= htmlspecialchars($message['sender_name']) ?></dd></div>
                    <div><dt>E-mail</dt><dd><?= htmlspecialchars($message['sender_email']) ?></dd></div>
                    <div><dt>Tárgy</dt><dd><?= htmlspecialchars($message['subject']) ?></dd></div>
                    <div><dt>Küldés ideje</dt><dd><?= htmlspecialchars($message['created_at']) ?></dd></div>
                    <div><dt>Bejelentkezett felhasználó</dt><dd><?= htmlspecialchars(($message['family_name'] ?? '') !== '' ? $message['family_name'] . ' ' . $message['given_name'] . ' (' . $message['username'] . ')' : 'Vendég') ?></dd></div>
                    <div><dt>Üzenet</dt><dd><?= nl2br(htmlspecialchars($message['body'])) ?></dd></div>
                </dl>
            </section>
        <?php elseif ($page === 'messages'): ?>
            <section class="page-head">
                <h1>Üzenetek</h1>
                <p>A beküldött üzenetek fordított időrendben jelennek meg.</p>
            </section>
            <section class="content-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Küldés ideje</th>
                            <th>Név</th>
                            <th>E-mail</th>
                            <th>Tárgy</th>
                            <th>Üzenet</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($messages as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['created_at']) ?></td>
                                <td><?= htmlspecialchars(($entry['family_name'] ?? '') !== '' ? $entry['family_name'] . ' ' . $entry['given_name'] : 'Vendég') ?></td>
                                <td><?= htmlspecialchars($entry['sender_email']) ?></td>
                                <td><?= htmlspecialchars($entry['subject']) ?></td>
                                <td><?= htmlspecialchars($entry['body']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php elseif ($page === 'crud-list'): ?>
            <section class="page-head page-head-split">
                <div>
                    <h1>CRUD: receptek kezelése</h1>
                    <p>Lista, létrehozás, szerkesztés és törlés útvonalakkal.</p>
                </div>
                <a class="button button-primary" href="<?= htmlspecialchars($baseUrl) ?>/crud/uj">Új recept</a>
            </section>
            <section class="content-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Név</th>
                            <th>Kategória</th>
                            <th>Leírás</th>
                            <th>Nehézség</th>
                            <th>Idő</th>
                            <th>Hozzávaló</th>
                            <th>Művelet</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recipes as $recipe): ?>
                            <tr>
                                <td><?= htmlspecialchars($recipe['name']) ?></td>
                                <td><?= htmlspecialchars($recipe['category_name']) ?></td>
                                <td><?= htmlspecialchars($recipe['summary']) ?></td>
                                <td><?= htmlspecialchars($recipe['difficulty']) ?></td>
                                <td><?= (int) $recipe['prep_minutes'] ?> perc</td>
                                <td><?= (int) $recipe['ingredient_count'] ?> db</td>
                                <td class="actions">
                                    <a href="<?= htmlspecialchars($baseUrl) ?>/crud/szerkesztes/<?= (int) $recipe['id'] ?>">Szerkesztés</a>
                                    <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/crud/torles/<?= (int) $recipe['id'] ?>">
                                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <button type="submit">Törlés</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php elseif ($page === 'crud-form'): ?>
            <section class="page-head">
                <h1><?= $recipeId === null ? 'Új recept létrehozása' : 'Recept szerkesztése' ?></h1>
            </section>
            <section class="content-card form-card">
                <form method="post" novalidate data-validate>
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="form-grid">
                        <label>
                            <span>Recept neve</span>
                            <input type="text" name="name" value="<?= htmlspecialchars($recipe['name']) ?>" data-required data-minlength="3">
                            <small class="error-text"><?= htmlspecialchars($errors['name'] ?? '') ?></small>
                        </label>
                        <label>
                            <span>Kategória</span>
                            <select name="category_id" data-required>
                                <option value="">Válassz</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= (string) $recipe['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="error-text"><?= htmlspecialchars($errors['category_id'] ?? '') ?></small>
                        </label>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Nehézség</span>
                            <select name="difficulty">
                                <option value="konnyu" <?= $recipe['difficulty'] === 'konnyu' ? 'selected' : '' ?>>könnyű</option>
                                <option value="kozepes" <?= $recipe['difficulty'] === 'kozepes' ? 'selected' : '' ?>>közepes</option>
                                <option value="halado" <?= $recipe['difficulty'] === 'halado' ? 'selected' : '' ?>>haladó</option>
                            </select>
                        </label>
                        <label>
                            <span>Elkészítési idő (perc)</span>
                            <input type="number" name="prep_minutes" min="5" max="300" value="<?= (int) $recipe['prep_minutes'] ?>" data-required>
                            <small class="error-text"><?= htmlspecialchars($errors['prep_minutes'] ?? '') ?></small>
                        </label>
                    </div>
                    <label>
                        <span>Kiemelt kép URL</span>
                        <input type="text" name="featured_image" value="<?= htmlspecialchars($recipe['featured_image'] ?? '') ?>">
                    </label>
                    <label>
                        <span>Rövid leírás</span>
                        <textarea name="summary" rows="5" data-required data-minlength="15"><?= htmlspecialchars($recipe['summary']) ?></textarea>
                        <small class="error-text"><?= htmlspecialchars($errors['summary'] ?? '') ?></small>
                    </label>
                    <button class="button button-primary" type="submit"><?= $recipeId === null ? 'Mentés' : 'Frissítés' ?></button>
                </form>
            </section>
        <?php elseif ($page === 'auth'): ?>
            <section class="page-head">
                <h1>Belépés és regisztráció</h1>
                <p>A regisztráció után a rendszer nem léptet be automatikusan.</p>
            </section>
            <section class="auth-grid">
                <article class="content-card form-card">
                    <h2>Belépés</h2>
                    <form method="post" novalidate data-validate>
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="form_type" value="login">
                        <label>
                            <span>Login név</span>
                            <input type="text" name="username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" data-required>
                        </label>
                        <label>
                            <span>Jelszó</span>
                            <input type="password" name="password" data-required>
                        </label>
                        <small class="error-text"><?= htmlspecialchars($errors['login'] ?? '') ?></small>
                        <button class="button button-primary" type="submit">Belépés</button>
                    </form>
                </article>
                <article class="content-card form-card">
                    <h2>Regisztráció</h2>
                    <form method="post" novalidate data-validate>
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="form_type" value="register">
                        <div class="form-grid">
                            <label>
                                <span>Családi név</span>
                                <input type="text" name="family_name" value="<?= htmlspecialchars($old['familyName'] ?? '') ?>" data-required>
                            </label>
                            <label>
                                <span>Utónév</span>
                                <input type="text" name="given_name" value="<?= htmlspecialchars($old['givenName'] ?? '') ?>" data-required>
                            </label>
                        </div>
                        <small class="error-text"><?= htmlspecialchars($errors['name'] ?? '') ?></small>
                        <label>
                            <span>Login név</span>
                            <input type="text" name="register_username" value="<?= htmlspecialchars($old['username'] ?? '') ?>" data-required data-minlength="4">
                            <small class="error-text"><?= htmlspecialchars($errors['register_username'] ?? '') ?></small>
                        </label>
                        <label>
                            <span>E-mail</span>
                            <input type="email" name="register_email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" data-required data-email>
                            <small class="error-text"><?= htmlspecialchars($errors['register_email'] ?? '') ?></small>
                        </label>
                        <label>
                            <span>Jelszó</span>
                            <input type="password" name="register_password" data-required data-minlength="8">
                            <small class="error-text"><?= htmlspecialchars($errors['register_password'] ?? '') ?></small>
                        </label>
                        <button class="button button-primary" type="submit">Regisztráció</button>
                    </form>
                </article>
            </section>
        <?php else: ?>
            <section class="content-card">
                <h1>Az oldal nem található</h1>
                <p>A kért tartalom nem érhető el.</p>
            </section>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="shell">
            <p><?= htmlspecialchars($appName) ?> • Web-programozás 1 beadandó • Front-controller, PHP, HTML5, CSS, JavaScript</p>
        </div>
    </footer>
    <script src="<?= htmlspecialchars($baseUrl) ?>/public/assets/js/app.js"></script>
</body>
</html>
