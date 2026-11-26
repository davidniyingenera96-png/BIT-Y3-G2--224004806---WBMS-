<div class="head"><div><h1><?= icon('edit') ?> Correct Reading</h1><p class="muted"><?= e($r['full_name']) ?> · Meter <?= e($r['meter_no']) ?></p></div><?= act_link('view', 'Back to readings', url('readings/index')) ?></div>
<div class="panel narrow"><?php foreach ($errors as $x): ?><div class="alert error" role="alert"><?= e($x) ?></div><?php endforeach; ?>
<?php if (!$ok): ?><div class="alert error" role="alert">Only the latest reading that has not been billed can be corrected or deleted.</div><?php endif; ?>
<form method="post" action="<?= url('readings/update') ?>" data-validate><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['reading_id'] ?>">
<label for="rd">Reading date</label><input id="rd" type="date" name="reading_date" required max="<?= date('Y-m-d') ?>" value="<?= e($r['reading_date']) ?>" <?= $ok ? '' : 'disabled' ?>>
<label for="rv">Meter value</label><input id="rv" type="number" step="0.01" min="0" name="meter_value" required value="<?= e($r['meter_value']) ?>" <?= $ok ? '' : 'disabled' ?>>
<button class="btn" <?= $ok ? '' : 'disabled' ?>><?= icon('save') ?>Save correction</button></form>
<?php if ($ok): ?><div class="acts2 mt"><?= act_form('del', 'Delete reading', 'readings/delete', ['id' => $r['reading_id']], 'Delete this reading?') ?></div><?php endif; ?></div>
