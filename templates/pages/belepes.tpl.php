<section class="page-head">
    <h1>Belépés és regisztráció</h1>
    <p>A regisztráció után a rendszer nem léptet be automatikusan.</p>
</section>
<section class="auth-grid">
    <article class="content-card form-card">
        <h2>Belépés</h2>
        <form method="post" action="<?= htmlspecialchars(app_url('belep')) ?>" novalidate data-validate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <label>
                <span>Login név</span>
                <input type="text" name="felhasznalo" value="<?= htmlspecialchars($old['username'] ?? '') ?>" data-required>
            </label>
            <label>
                <span>Jelszó</span>
                <input type="password" name="jelszo" data-required>
            </label>
            <small class="error-text"><?= htmlspecialchars($errors['login'] ?? '') ?></small>
            <button class="button button-primary" type="submit">Belépés</button>
        </form>
    </article>
    <article class="content-card form-card">
        <h2>Regisztráció</h2>
        <form method="post" action="<?= htmlspecialchars(app_url('regisztral')) ?>" novalidate data-validate>
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="form-grid">
                <label>
                    <span>Családi név</span>
                    <input type="text" name="vezeteknev" value="<?= htmlspecialchars($old['familyName'] ?? '') ?>" data-required>
                </label>
                <label>
                    <span>Utónév</span>
                    <input type="text" name="utonev" value="<?= htmlspecialchars($old['givenName'] ?? '') ?>" data-required>
                </label>
            </div>
            <small class="error-text"><?= htmlspecialchars($errors['name'] ?? '') ?></small>
            <label>
                <span>Login név</span>
                <input type="text" name="felhasznalo" value="<?= htmlspecialchars($old['username'] ?? '') ?>" data-required data-minlength="4">
                <small class="error-text"><?= htmlspecialchars($errors['register_username'] ?? '') ?></small>
            </label>
            <label>
                <span>E-mail</span>
                <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" data-required data-email>
                <small class="error-text"><?= htmlspecialchars($errors['register_email'] ?? '') ?></small>
            </label>
            <label>
                <span>Jelszó</span>
                <input type="password" name="jelszo" data-required data-minlength="8">
                <small class="error-text"><?= htmlspecialchars($errors['register_password'] ?? '') ?></small>
            </label>
            <button class="button button-primary" type="submit">Regisztráció</button>
        </form>
    </article>
</section>
