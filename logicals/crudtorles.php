<?php
app_guard_csrf();

$recipeId = (int) ($keres['params']['id'] ?? 0);
$statement = app_pdo()->prepare('DELETE FROM recipes WHERE id = :id');
$statement->execute(['id' => $recipeId]);

app_flash('success', 'A recept törölve lett.');
app_redirect('crud');
