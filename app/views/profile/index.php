<div class="head"><div><h1><?= icon('user') ?> My Profile</h1><p class="muted">Keep your contact details up to date.</p></div></div>
<div class="grid2"><div class="panel"><h2>Account &amp; personal details</h2><form method="post" action="<?= url('profile/save') ?>" data-validate><?= csrf_field() ?>
<label>Username</label><input value="<?= e($acct['username']) ?>" disabled><label>Role</label><input value="<?= e(ucfirst($acct['role'])) ?>" disabled>
<label for="em">Email</label><input id="em" type="email" name="email" required maxlength="120" value="<?= e($acct['email']) ?>">
<?php if ($cust): ?><label>Full name</label><input value="<?= e($cust['full_name']) ?>" disabled><label>Meter number</label><input value="<?= e($cust['meter_no']) ?>" disabled>
<label for="ad">Registered address</label><input id="ad" name="address" required maxlength="255" value="<?= e($cust['address']) ?>"><label for="ph">Phone</label><input id="ph" name="phone" value="<?= e($cust['phone']) ?>"><p class="muted small">Name and meter number can only be changed by staff.</p><?php endif; ?>
<button class="btn">Save profile</button></form></div>
<div class="panel"><h2>Change password</h2><form method="post" action="<?= url('profile/password') ?>" data-validate><?= csrf_field() ?><label for="p1">Current password</label><input id="p1" type="password" name="current" required autocomplete="current-password">
<label for="p2">New password</label><input id="p2" type="password" name="new" required minlength="8" autocomplete="new-password"><label for="p3">Confirm new password</label><input id="p3" type="password" name="confirm" required minlength="8" autocomplete="new-password"><button class="btn">Change password</button></form></div></div>
