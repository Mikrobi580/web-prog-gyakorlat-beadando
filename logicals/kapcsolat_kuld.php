<?php
app_guard_csrf();

$user = app_current_user();
$senderName = trim((string) ($_POST['sender_name'] ?? ''));
$senderEmail = trim((string) ($_POST['sender_email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$body = trim((string) ($_POST['body'] ?? ''));
$errors = [];

if ($senderName === '' || mb_strlen($senderName) < 3) {
    $errors['sender_name'] = 'Adj meg legalább 3 karakteres nevet.';
}
if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
    $errors['sender_email'] = 'Érvényes e-mail címet adj meg.';
}
if ($subject === '' || mb_strlen($subject) < 5) {
    $errors['subject'] = 'A tárgy legalább 5 karakter legyen.';
}
if ($body === '' || mb_strlen($body) < 20) {
    $errors['body'] = 'Az üzenet legalább 20 karakter legyen.';
}

if ($errors !== []) {
    app_set_errors($errors);
    app_set_old(compact('senderName', 'senderEmail', 'subject', 'body'));
    app_redirect('kapcsolat');
}

$statement = app_pdo()->prepare(
    'INSERT INTO messages (sender_name, sender_email, subject, body, user_id, created_at)
     VALUES (:sender_name, :sender_email, :subject, :body, :user_id, :created_at)'
);
$statement->execute([
    'sender_name' => $senderName,
    'sender_email' => $senderEmail,
    'subject' => $subject,
    'body' => $body,
    'user_id' => $user['id'] ?? null,
    'created_at' => date('Y-m-d H:i:s'),
]);

$messageId = (int) app_pdo()->lastInsertId();
app_flash('success', 'Az üzenetet elmentettük.');
app_redirect('kapcsolat/siker/' . $messageId);
