<?php
$recipeId = isset($keres['params']['id']) ? (int) $keres['params']['id'] : null;
$recipe = array_merge(app_recipe($recipeId), $old);
$categories = app_categories();
$action = $recipeId === null ? 'crudmentes' : 'crud/frissites/' . $recipeId;
?>
<section class="page-head">
    <h1><?= $recipeId === null ? 'Új recept létrehozása' : 'Recept szerkesztése' ?></h1>
</section>
<section class="content-card form-card">
    <form method="post" action="<?= htmlspecialchars(app_url($action)) ?>" novalidate data-validate>
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
