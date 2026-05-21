<?php
app_guard_csrf();

$payload = app_recipe_payload();
$errors = app_validate_recipe($payload);

if ($errors !== []) {
    app_set_errors($errors);
    app_set_old($payload);
    app_redirect('crud/uj');
}

$nextId = (int) app_pdo()->query('SELECT COALESCE(MAX(id), 0) + 1 FROM recipes')->fetchColumn();
$statement = app_pdo()->prepare(
    'INSERT INTO recipes (id, name, category_id, created_at, published_at, summary, difficulty, prep_minutes, featured_image)
     VALUES (:id, :name, :category_id, :created_at, :published_at, :summary, :difficulty, :prep_minutes, :featured_image)'
);
$statement->execute([
    'id' => $nextId,
    'name' => $payload['name'],
    'category_id' => $payload['category_id'],
    'created_at' => date('Y-m-d'),
    'published_at' => date('Y-m-d'),
    'summary' => $payload['summary'],
    'difficulty' => $payload['difficulty'],
    'prep_minutes' => $payload['prep_minutes'],
    'featured_image' => $payload['featured_image'],
]);

app_flash('success', 'Az új recept bekerült az adatbázisba.');
app_redirect('crud');
