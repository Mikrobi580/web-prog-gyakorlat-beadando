<?php
$images = app_gallery_images();
$currentUser = app_current_user();
?>
<section class="page-head">
    <h1>Képgaléria</h1>
    <p>Receptekhez kapcsolódó hangulatképek és felhasználói feltöltések.</p>
</section>
<?php if ($currentUser): ?>
    <section class="content-card form-card">
        <h2>Új kép feltöltése</h2>
        <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars(app_url('kepfeltoltes')) ?>" novalidate data-validate>
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
        $imageSrc = $image['filename'] ? app_url('/storage/uploads/' . $image['filename']) : $image['external_url'];
        $author = $image['family_name'] ? $image['family_name'] . ' ' . $image['given_name'] : 'Konyhatár szerkesztőség';
        $canDelete = $currentUser && (int) ($image['uploaded_by_user_id'] ?? 0) === (int) $currentUser['id'];
        ?>
        <article class="gallery-card">
            <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
            <div>
                <h3><?= htmlspecialchars($image['title']) ?></h3>
                <p><?= htmlspecialchars($image['description']) ?></p>
                <small><?= htmlspecialchars($author) ?> • <?= htmlspecialchars($image['created_at']) ?></small>
                <?php if ($canDelete): ?>
                    <form method="post" action="<?= htmlspecialchars(app_url('keptorles/' . $image['id'])) ?>" class="inline-form">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="button-link danger-link" onclick="return confirm('Biztosan törölni szeretnéd ezt a képet?');">Saját kép törlése</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>
