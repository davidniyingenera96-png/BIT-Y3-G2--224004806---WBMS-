<div class="cardhead"><h2>Create Account</h2><p class="muted">Create your customer profile and online account</p></div>
<?php foreach ($errs as $x): ?><div class="alert error" role="alert"><?= e($x) ?></div><?php endforeach; ?>
<form method="post" action="<?= url('auth/register') ?>" data-validate><?= csrf_field() ?><input type="hidden" name="role" id="regRole" value="customer">
<div class="roles" role="group" aria-label="Register as"><?php foreach ([['customer', 'users', 'Customer'], ['staff', 'wrench', 'Staff'], ['admin', 'shield', 'Admin']] as [$rv, $ri, $rl]): ?><button type="button" class="regrole <?= $rv === 'customer' ? 'on' : '' ?>" data-role="<?= $rv ?>"><?= icon($ri) ?><?= $rl ?></button><?php endforeach; ?></div>
<div class="alert error" id="roleNote" hidden>Staff and administrator accounts are created by the system administrator. Please contact your administrator.</div>
<label for="fn">Full name</label><input id="fn" name="full_name" required maxlength="120" value="<?= e($v['full_name']) ?>">
<label for="m">Meter number</label><input id="m" name="meter_no" required pattern="[A-Za-z0-9-]{3,30}" placeholder="e.g. MT-004" value="<?= e($v['meter_no']) ?>">
<label for="ad">Address</label><input id="ad" name="address" required maxlength="255" value="<?= e($v['address']) ?>">
<label for="ph">Phone (optional)</label><input id="ph" name="phone" value="<?= e($v['phone']) ?>">
<p class="muted small">If our staff already registered your meter, enter the same name to link your account. Otherwise a new profile is created.</p>
<label for="u">Username</label><input id="u" name="username" required pattern="[A-Za-z0-9._-]{3,50}" value="<?= e($v['username']) ?>">
<label for="e">Email</label><input id="e" type="email" name="email" required value="<?= e($v['email']) ?>">
<label for="p">Password</label><div class="pw"><input id="p" type="password" name="password" required minlength="8" autocomplete="new-password"><button type="button" aria-label="Show password">👁</button></div>
<label for="c">Confirm password</label><input id="c" type="password" name="confirm" required minlength="8" autocomplete="new-password">
<button type="submit" class="btn" id="regSubmit">Create account</button></form><p class="muted small">Already registered? <a href="<?= url('auth/login') ?>">Login</a></p>
