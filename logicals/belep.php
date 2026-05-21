<?php
app_guard_csrf();

$username = trim((string) ($_POST['felhasznalo'] ?? ''));
$password = (string) ($_POST['jelszo'] ?? '');

$statement = app_pdo()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
$statement->execute(['username' => $username]);
$user = $statement->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    app_set_errors(['login' => 'Hibás felhasználónév vagy jelszó.']);
    app_set_old(['username' => $username]);
    app_redirect('belepes');
}

app_login_session($user);
app_flash('success', 'Sikeres belépés.');
app_redirect('/');
