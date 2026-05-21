<?php
app_guard_csrf();

$familyName = trim((string) ($_POST['vezeteknev'] ?? ''));
$givenName = trim((string) ($_POST['utonev'] ?? ''));
$username = trim((string) ($_POST['felhasznalo'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['jelszo'] ?? '');
$errors = [];

if ($familyName === '' || $givenName === '') {
    $errors['name'] = 'A családi név és az utónév kitöltése kötelező.';
}
if ($username === '' || mb_strlen($username) < 4) {
    $errors['register_username'] = 'A login név legalább 4 karakter legyen.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['register_email'] = 'Érvényes e-mail címet adj meg.';
}
if (mb_strlen($password) < 8) {
    $errors['register_password'] = 'A jelszó legalább 8 karakter legyen.';
}

$exists = app_pdo()->prepare('SELECT COUNT(*) FROM users WHERE username = :username OR email = :email');
$exists->execute(['username' => $username, 'email' => $email]);
if ((int) $exists->fetchColumn() > 0) {
    $errors['register_username'] = 'Ez a login név vagy e-mail már foglalt.';
}

if ($errors !== []) {
    app_set_errors($errors);
    app_set_old([
        'familyName' => $familyName,
        'givenName' => $givenName,
        'username' => $username,
        'email' => $email,
    ]);
    app_redirect('belepes');
}

$statement = app_pdo()->prepare(
    'INSERT INTO users (family_name, given_name, username, email, password_hash, created_at)
     VALUES (:family_name, :given_name, :username, :email, :password_hash, :created_at)'
);
$statement->execute([
    'family_name' => $familyName,
    'given_name' => $givenName,
    'username' => $username,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'created_at' => date('Y-m-d H:i:s'),
]);

app_flash('success', 'Sikeres regisztráció. Most már be tudsz lépni.');
app_redirect('belepes');
