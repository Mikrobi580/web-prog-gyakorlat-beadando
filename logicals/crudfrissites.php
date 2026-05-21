<?php
app_guard_csrf();

$recipeId = (int) ($keres['params']['id'] ?? 0);
$payload = app_recipe_payload();
$errors = app_validate_recipe($payload);

if ($errors !== []) {
    app_set_errors($errors);
    app_set_old($payload);
    app_redirect('crud/szerkesztes/' . $recipeId);
}

$statement = app_pdo()->prepare(
    'UPDATE recipes
     SET name = :name,
         category_id = :category_id,
         summary = :summary,
         difficulty = :difficulty,
         prep_minutes = :prep_minutes,
         featured_image = :featured_image
     WHERE id = :id'
);
$statement->execute([
    'id' => $recipeId,
    'name' => $payload['name'],
    'category_id' => $payload['category_id'],
    'summary' => $payload['summary'],
    'difficulty' => $payload['difficulty'],
    'prep_minutes' => $payload['prep_minutes'],
    'featured_image' => $payload['featured_image'],
]);

app_flash('success', 'A recept adatai frissültek.');
app_redirect('crud');
