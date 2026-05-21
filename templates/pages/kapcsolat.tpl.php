<?php $defaultSenderName = $currentUser ? $currentUser['family_name'] . ' ' . $currentUser['given_name'] : ''; ?>
<section class="page-head">
    <h1>Kapcsolat</h1>
    <p>Küldj üzenetet az oldal tulajdonosának. Az üzenet külön oldalon megjelenik és adatbázisba is mentésre kerül.</p>
</section>
<section class="content-card form-card">
    <form method="post" action="<?= htmlspecialchars(app_url('kapcsolat_kuld')) ?>" novalidate data-validate>
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="form-grid">
            <label>
                <span>Név</span>
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
