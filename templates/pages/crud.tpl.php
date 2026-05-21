<?php $recipes = app_crud_recipes(); ?>
<section class="page-head page-head-split">
    <div>
        <h1>CRUD: receptek kezelése</h1>
        <p>Lista, létrehozás, szerkesztés és törlés útvonalakkal.</p>
    </div>
    <a class="button button-primary" href="<?= htmlspecialchars(app_url('crud/uj')) ?>">Új recept</a>
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
                        <a href="<?= htmlspecialchars(app_url('crud/szerkesztes/' . $recipe['id'])) ?>">Szerkesztés</a>
                        <form method="post" action="<?= htmlspecialchars(app_url('crud/torles/' . $recipe['id'])) ?>">
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
