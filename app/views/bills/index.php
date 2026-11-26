<h1><?= e($title) ?></h1>
<?php if (!$own): ?><div class="panel narrow"><h2>Generate bill</h2><form method="post" action="<?= url('bills/generate') ?>" data-validate><?= csrf_field() ?>
<label for="cu">Customer</label><select id="cu" name="customer_id" required><option value="">Select customer…</option><?php foreach ($cust as $c): ?><option value="<?= (int)$c['customer_id'] ?>"><?= e($c['full_name'] . ' (' . $c['meter_no'] . ')') ?></option><?php endforeach; ?></select>
<label for="mo">Billing month</label><input id="mo" type="month" name="month" required value="<?= date('Y-m') ?>"><button class="btn">Generate bill</button></form></div>
<form method="get" class="filter"><input type="hidden" name="r" value="bills/index"><select name="status" aria-label="Filter by status"><option value="">All statuses</option><?php foreach (['Unpaid', 'Partially Paid', 'Paid', 'Cancelled'] as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select><button class="btn sm">Filter</button></form><?php endif; ?>
<div class="panel"><?php require __DIR__ . '/_table.php'; ?></div>
