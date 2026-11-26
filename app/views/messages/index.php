<div class="head"><div><h1><?= icon('mail') ?> Contact Messages</h1><p class="muted">Messages sent from the public Contact page.</p></div></div>
<?php foreach ($rows as $m): ?><div class="panel msg"><div class="head"><b><?= e($m['subject']) ?> <?php if (!$m['is_read']): ?><span class="badge b-Unpaid">New</span><?php endif; ?></b><div class="acts2"><small class="muted"><?= e($m['created_at']) ?></small><?= act_form('del', 'Delete', 'messages/delete', ['id' => $m['message_id']], 'Delete this message?') ?></div></div>
<p class="muted"><?= e($m['name']) ?> · <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></p><p><?= nl2br(e($m['message'])) ?></p></div><?php endforeach; ?>
<?php if (!$rows): ?><div class="panel muted">No messages yet.</div><?php endif; ?>
