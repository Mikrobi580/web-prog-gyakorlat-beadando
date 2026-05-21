<?php $messages = app_messages(); ?>
<section class="page-head">
    <h1>Üzenetek</h1>
    <p>A beküldött üzenetek fordított időrendben jelennek meg.</p>
</section>
<section class="content-card">
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Küldés ideje</th>
                <th>Név</th>
                <th>E-mail</th>
                <th>Tárgy</th>
                <th>Üzenet</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['created_at']) ?></td>
                    <td><?= htmlspecialchars(($entry['family_name'] ?? '') !== '' ? $entry['family_name'] . ' ' . $entry['given_name'] : 'Vendég') ?></td>
                    <td><?= htmlspecialchars($entry['sender_email']) ?></td>
                    <td><?= htmlspecialchars($entry['subject']) ?></td>
                    <td><?= htmlspecialchars($entry['body']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
