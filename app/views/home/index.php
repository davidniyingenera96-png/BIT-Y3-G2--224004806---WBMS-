<section class="welcome"><div class="wc">
<span class="eyebrow2">Welcome</span>
<h1>Welcome to the<span><?= e(site('site_name')) ?></span></h1>
<p class="wtag"><?= e(site('tagline')) ?></p>
<p class="wp">Your one-stop platform to view your meter readings, check your water bills, make payments and follow your payment history — anytime, anywhere. Clean water, billed fairly and managed with care.</p>
<div class="cta2"><a class="btn sm" href="<?= url('auth/login') ?>"><?= icon('login') ?>Login</a><a class="btn sm light" href="<?= url('auth/register') ?>"><?= icon('userplus') ?>Create Account</a></div>
<div class="wroles"><span>Sign in as</span><?php foreach ([['customer', 'users', 'Customer'], ['staff', 'wrench', 'Staff'], ['admin', 'shield', 'Admin']] as [$rv, $ri, $rl]): ?><a href="<?= url('auth/login', ['role' => $rv]) ?>"><?= icon($ri) ?><?= $rl ?></a><?php endforeach; ?></div>
</div>
<div class="whl"><div><?= icon('gauge') ?>Meter readings</div><div><?= icon('file') ?>Accurate bills</div><div><?= icon('card') ?>Easy payments</div><div><?= icon('shield') ?>Secure access</div></div></section>
