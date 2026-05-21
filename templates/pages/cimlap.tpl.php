<?php
$featuredRecipes = app_featured_recipes();
$stats = app_stats();
?>
<section class="hero">
    <div>
        <p class="eyebrow">Otthoni konyha, adatbázis-alapon</p>
        <h1>Receptgyűjteményből épített, modern PHP webalkalmazás.</h1>
        <p class="hero-copy">A Konyhatár a választott recept-adatbázist látványos, reszponzív felülettel, galériával, kapcsolatoldallal, üzenetkezeléssel és teljes CRUD modullal egészíti ki.</p>
        <div class="hero-actions">
            <a class="button button-primary" href="<?= htmlspecialchars(app_url('crud')) ?>">Receptadatbázis megnyitása</a>
            <a class="button button-secondary" href="<?= htmlspecialchars(app_url('kapcsolat')) ?>">Üzenet küldése</a>
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
            <source src="<?= htmlspecialchars(app_url('/public/assets/media/konyhatar-intro.mp4')) ?>" type="video/mp4">
        </video>
    </article>
    <article class="content-card">
        <h2>YouTube videó</h2>
        <iframe class="media-frame" src="https://www.youtube.com/embed/xPPLbEFbCAo?si=yb9qQ-4NQ-rjH2Mo" title="YouTube video" allowfullscreen></iframe>
    </article>
</section>

<section class="content-card map-card">
    <div>
        <h2>Hol található a bemutató konyha?</h2>
        <p>A felület mintacímként egy budapesti gasztropontot mutat. A beágyazott Google térkép a beadandó egyik kötelező elemét valósítja meg.</p>
    </div>
    <iframe src="https://www.google.com/maps?q=Budapest%20R%C3%A1day%20utca%2013&amp;output=embed" class="map-frame" loading="lazy" title="Google térkép"></iframe>
</section>
