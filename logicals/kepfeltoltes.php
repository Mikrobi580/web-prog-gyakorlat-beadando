<?php
app_require_login();
app_guard_csrf();

$title = trim((string) ($_POST['title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$file = $_FILES['image'] ?? null;
$errors = [];

if ($title === '' || mb_strlen($title) < 3) {
    $errors['title'] = 'A cím legalább 3 karakter legyen.';
}

if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $errors['image'] = 'Válassz egy feltöltendő képet.';
} else {
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        $errors['image'] = 'Csak JPG, PNG, WEBP vagy GIF fájl tölthető fel.';
    }
}

if ($errors !== []) {
    app_set_errors($errors);
    app_set_old(['title' => $title, 'description' => $description]);
    app_redirect('kepek');
}

$extension = $allowed[$mime];
$filename = sprintf('%s.%s', bin2hex(random_bytes(10)), $extension);
$target = app_base_path() . '/storage/uploads/' . $filename;
move_uploaded_file($file['tmp_name'], $target);

$statement = app_pdo()->prepare(
    'INSERT INTO gallery_images (title, description, filename, external_url, uploaded_by_user_id, created_at)
     VALUES (:title, :description, :filename, NULL, :user_id, :created_at)'
);
$statement->execute([
    'title' => $title,
    'description' => $description,
    'filename' => $filename,
    'user_id' => app_current_user()['id'],
    'created_at' => date('Y-m-d H:i:s'),
]);

app_flash('success', 'A kép sikeresen feltöltve.');
app_redirect('kepek');
