<?php $edit = !empty($c['customer_id']); ?><div class="head"><h1><?= icon($edit ? 'edit' : 'plus') ?> <?= e($title) ?></h1><?= act_link('view', 'Back to customers', url('customers/index')) ?></div>
<div class="panel narrow"><?php foreach ($errors as $x): ?><div class="alert error" role="alert"><?= e($x) ?></div><?php endforeach; ?>
<form method="post" action="<?= url('customers/save') ?>" data-validate><?= csrf_field() ?><input type="hidden" name="id" value="<?= $edit ? (int)$c['customer_id'] : '' ?>">
<label for="n">Full name</label><input id="n" name="full_name" required maxlength="120" value="<?= e($c['full_name'] ?? '') ?>">
<label for="mn">Meter number</label><input id="mn" name="meter_no" required pattern="[A-Za-z0-9-]{3,30}" value="<?= e($c['meter_no'] ?? '') ?>">
<label for="a">Address</label><input id="a" name="address" required maxlength="255" value="<?= e($c['address'] ?? '') ?>">
<label for="ph">Phone (optional)</label><input id="ph" name="phone" value="<?= e($c['phone'] ?? '') ?>"><button class="btn"><?= icon('save') ?>Save customer</button></form></div>
<?php if ($edit): ?><div class="panel narrow"><h2>Manage customer</h2><div class="acts2"><?= act_form('warn', 'Deactivate', 'customers/deactivate', ['id' => $c['customer_id']], 'Deactivate this customer? Billing history is kept.') ?>
<?php if ($u['role'] === 'admin'): ?><?= act_form('del', 'Delete', 'customers/delete', ['id' => $c['customer_id']], 'Delete permanently? Only possible when there is no reading or bill history.') ?><?php endif; ?></div>
<p class="muted small">Deactivating keeps all readings, bills and payments. Delete is only allowed for customers with no history.</p></div><?php endif; ?>
