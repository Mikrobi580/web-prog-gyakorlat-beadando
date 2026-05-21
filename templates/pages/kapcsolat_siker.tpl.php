<?php
$message = app_message((int) ($keres['params']['id'] ?? 0));
if (!$message) {
    header('HTTP/1.0 404 Not Found');
    echo '<section class="content-card"><h1>Az oldal nem található</h1><p>A kért üzenet nem érhető el.</p></section>';
    return;
}
?>
<section class="content-card">
    <h1>Elküldött üzenet</h1>
    <p>Az űrlap adatai mentésre kerültek, és ez az oldal külön tartalomként megjeleníti őket.</p>
    <dl class="message-detail">
        <div><dt>Név</dt><dd><?= htmlspecialchars($message['sender_name']) ?></dd></div>
        <div><dt>E-mail</dt><dd><?= htmlspecialchars($message['sender_email']) ?></dd></div>
        <div><dt>Tárgy</dt><dd><?= htmlspecialchars($message['subject']) ?></dd></div>
        <div><dt>Küldés ideje</dt><dd><?= htmlspecialchars($message['created_at']) ?></dd></div>
        <div><dt>Bejelentkezett felhasználó</dt><dd><?= htmlspecialchars(($message['family_name'] ?? '') !== '' ? $message['family_name'] . ' ' . $message['given_name'] . ' (' . $message['username'] . ')' : 'Vendég') ?></dd></div>
        <div><dt>Üzenet</dt><dd><?= nl2br(htmlspecialchars($message['body'])) ?></dd></div>
    </dl>
</section>
