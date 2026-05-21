<?php
app_require_login();
app_guard_csrf();

$imageId = (int) ($keres['params']['id'] ?? 0);
$statement = app_pdo()->prepare(
    'SELECT id, filename, uploaded_by_user_id
     FROM gallery_images
     WHERE id = :id
     LIMIT 1'
);
$statement->execute(['id' => $imageId]);
$image = $statement->fetch();

if (!$image) {
    app_flash('error', 'A kép nem található.');
    app_redirect('kepek');
}

$currentUser = app_current_user();
if (!$currentUser || (int) $image['uploaded_by_user_id'] !== (int) $currentUser['id']) {
    app_flash('error', 'Csak a saját feltöltött képed törölheted.');
    app_redirect('kepek');
}

$deleteStatement = app_pdo()->prepare('DELETE FROM gallery_images WHERE id = :id');
$deleteStatement->execute(['id' => $imageId]);

$filename = (string) ($image['filename'] ?? '');
if ($filename !== '') {
    $path = app_base_path() . '/storage/uploads/' . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}

app_flash('success', 'A saját feltöltött kép törölve lett.');
app_redirect('kepek');
