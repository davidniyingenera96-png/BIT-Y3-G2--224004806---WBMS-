<?php $role = $role ?? ''; $err = $err ?? ''; ?>
<div class="cardhead"><h2><?= user() ? 'Welcome back!' : 'Welcome Back!' ?></h2><p class="muted"><?= user() ? 'Signed in as ' . e(user()['username']) . ' (' . e(user()['role']) . ')' : 'Please login to your account' ?></p></div>
<?php if (user()): ?><a class="btn block" href="<?= url('dashboard') ?>">Go to my dashboard</a>
<form method="post" action="<?= url('auth/logout') ?>"><?= csrf_field() ?><button class="btn ghost">Logout</button></form>
<?php else: ?>
<?php if ($err): ?><div class="alert error" role="alert"><?= e($err) ?></div><?php endif; ?>
<form method="post" action="<?= url('auth/login') ?>" data-validate><?= csrf_field() ?><input type="hidden" name="role" id="roleInput" value="<?= e($role) ?>">
<div class="inp"><?= icon('user') ?><input name="identity" required autocomplete="username" placeholder="Username or Email" aria-label="Username or Email" value="<?= e($identity ?? '') ?>"></div>
<div class="inp pw"><?= icon('lock') ?><input type="password" name="password" required autocomplete="current-password" placeholder="Password" aria-label="Password"><button type="button" aria-label="Show password">👁</button></div>
<button type="submit" class="btn">Login</button></form>
<p class="muted small">Don't have an account? <a href="<?= url('auth/register') ?>">Register here</a></p>
<div class="or">OR</div>
<div class="roles" role="group" aria-label="Sign in as"><?php foreach ([['customer', 'users', 'Customer'], ['staff', 'wrench', 'Staff'], ['admin', 'shield', 'Admin']] as [$v, $i, $l]): ?>
<button type="button" class="rolebtn <?= $role === $v ? 'on' : '' ?>" data-role="<?= $v ?>"><?= icon($i) ?><?= $l ?></button><?php endforeach; ?></div>
<p class="muted small hint">Optional: choose your role to make sure you sign in to the right account.</p>
<?php endif; ?>
