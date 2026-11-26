<div class="head"><div><h1><?= icon('wrench') ?> Settings</h1><p class="muted">Change the logo, name, tagline and contact details, and set the billing rules.</p></div></div>
<?php foreach ($errors as $x): ?><div class="alert error" role="alert"><?= e($x) ?></div><?php endforeach; ?>
<div class="grid2"><div class="panel"><h2>Branding &amp; contact</h2>
<form method="post" action="<?= url('settings/branding') ?>" enctype="multipart/form-data" data-validate><?= csrf_field() ?>
<label>Current logo</label><div class="logoprev"><?= logo('prev') ?></div><label for="lg">Upload a new logo (PNG, JPG or WebP, max 1 MB)</label><input id="lg" type="file" name="logo" accept="image/png,image/jpeg,image/webp">
<?php if (site('logo') !== ''): ?><label class="chk"><input type="checkbox" name="remove_logo" value="1"> Remove current logo</label><?php endif; ?>
<?php foreach ([['site_name', 'Site name'], ['tagline', 'Tagline'], ['email', 'Contact email'], ['phone', 'Phone'], ['address', 'Address'], ['hours', 'Office hours']] as [$k, $l]): ?><label for="s_<?= $k ?>"><?= e($l) ?></label><input id="s_<?= $k ?>" name="<?= $k ?>" value="<?= e($s[$k] ?? '') ?>" <?= $k === 'site_name' ? 'required minlength="3"' : '' ?> maxlength="160"><?php endforeach; ?>
<button class="btn">Save branding &amp; contact</button></form></div>
<div class="panel"><h2>Billing rules</h2><p class="muted">Set the approved tariff. New values apply to bills generated from now on; old bills keep their own rates.</p>
<form method="post" action="<?= url('settings/billing') ?>" data-validate><?= csrf_field() ?>
<label for="b1">Unit rate (RWF per unit)</label><input id="b1" type="number" step="0.01" min="0.01" name="unit_rate" required value="<?= e($bs['unit_rate'] ?? '') ?>">
<label for="b2">Fixed charge (RWF)</label><input id="b2" type="number" step="0.01" min="0" name="fixed_charge" required value="<?= e($bs['fixed_charge'] ?? '0') ?>">
<label for="b3">Tax rate (%)</label><input id="b3" type="number" step="0.01" min="0" max="100" name="tax_rate" required value="<?= e($bs['tax_rate'] ?? '0') ?>">
<label for="b4">Due days after month end</label><input id="b4" type="number" min="1" max="90" name="due_days" required value="<?= e($bs['due_days'] ?? '15') ?>"><button class="btn">Save billing rules</button></form></div></div>
